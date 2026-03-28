<?php

namespace App\Domain\ContentExtraction\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Queue\Middleware\RateLimited;
use App\Domain\ContentExtraction\Services\PageFetcher;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Services\RunFinalizer;
use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Services\PageContentWriter;
use App\Domain\ContentExtraction\Services\ExtractionEventStore;
use App\Domain\ContentExtraction\Enums\PageExtractionFailureType;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;

class ExtractPageContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * On Azure F1, network blips are common, so we allow retries.
     */
    public int $tries = 5;

    /**
     * The website ID used for rate limiting.
     */
    public $websiteId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $pageExtractionId,
    ) {
        // Hydrate websiteId early so the middleware doesn't need to perform extra DB queries
        $this->websiteId = PageExtraction::find($pageExtractionId)?->run?->website_id;
    }

    /**
     * Get the middleware the job should pass through.
     */
    // public function middleware(): array
    // {
    //     return [new RateLimited('external-crawler')];
    // }

    /**
     * Execute the job.
     */
    public function handle(
        PageFetcher $fetcher,
        PageContentWriter $writer,
        RunFinalizer $finalizer,
        ExtractionEventStore $events,
    ): void {
        $ticket = PageExtraction::with(['page', 'run'])->find($this->pageExtractionId);

        // 1. Validation: Stop if ticket doesn't exist, is done, or run is not active
        if (
            !$ticket ||
            $ticket->status === PageExtractionStatus::Done ||
            $ticket->run->status !== ContentExtractionRunStatus::Running
        ) {
            return;
        }

        // Mark as processing immediately to avoid other workers picking it up
        $ticket->update([
            'status' => PageExtractionStatus::Processing,
            'started_at' => now(), // Set started_at here
        ]);

        $isTerminal = false;

        try {

            // 2. Fetch HTML
            $html = $fetcher->fetch($ticket->page);

            // 3. Parse Content
            $dom = new \DOMDocument();
            // Use @ to suppress warnings from malformed HTML
            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new \DOMXPath($dom);

            // Strip noise
            foreach ($xpath->query('//script|//style|//iframe|//noscript') as $node) {
                $node->parentNode->removeChild($node);
            }

            $jsonContent = [
                'head' => [
                    'title' => ($titleNode = $xpath->query('//title')->item(0)) ? $titleNode->nodeValue : '',
                ],
                'body' => [
                    'text' => trim(preg_replace('/\s+/', ' ', $dom->textContent)),
                ]
            ];

            // 4. Save Content
            $writer->write($ticket->page, $jsonContent);

            // 5. Success State
            $ticket->update([
                'status' => PageExtractionStatus::Done,
                'finished_at' => now(), // Set finished_at on success
            ]);

            $events->append($ticket->run, [
                'type' => 'page.done',
                'message' => "Extracted: {$ticket->page->url}"
            ]);

            // Increment and Finalize only once on success
            $ticket->run->increment('processed_pages');
            $finalizer->checkAndFinalize($ticket->run);
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();

            // 4. FAILURE PATH
            // If it's a 404/410 OR we've exhausted all 5 tries for other errors
            if ($statusCode === 404 || $statusCode === 410 || $this->attempts() >= $this->tries) {

                $failureType = PageExtractionFailureType::fromStatusCode($statusCode);

                $ticket->update([
                    'status' => PageExtractionStatus::Failed,
                    'failure_type' => $failureType,
                    'error' => $e->getMessage(),
                    'finished_at' => now(),
                ]);

                $events->append($ticket->run, [
                    'type' => 'page.failed',
                    'message' => "Final failure ({$failureType->value}): {$ticket->page->url}"
                ]);

                // Increment and Finalize only once on permanent failure
                $ticket->run->increment('processed_pages');
                $finalizer->checkAndFinalize($ticket->run);

                // Stop retrying
                $this->fail($e);
                return;
            }

            // 5. RETRY PATH
            // If we get here, it's a retryable error (like a 500 or timeout) 
            // and we still have attempts left. 
            // We do NOT increment processed_pages yet.
            throw $e;
        }
    }
}
