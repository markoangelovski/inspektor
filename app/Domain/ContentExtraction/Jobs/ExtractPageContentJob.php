<?php

namespace App\Domain\ContentExtraction\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new \DOMXPath($dom);

            $bodyNode = $xpath->query('//body')->item(0);
            $jsonContent = [
                'head' => $this->parseHead($xpath),
                'body' => $bodyNode ? ($this->parseElement($bodyNode) ?? []) : [],
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

    private function parseHead(\DOMXPath $xpath): array
    {
        $head = [];

        if ($title = $xpath->query('//head/title')->item(0)) {
            $head['title'] = trim($title->textContent);
        }

        if ($canonical = $xpath->query('//head/link[@rel="canonical"]')->item(0)) {
            $head['canonical'] = $canonical->getAttribute('href');
        }

        $relevantRels = ['alternate', 'prev', 'next', 'author', 'license', 'amphtml'];
        $links = [];
        foreach ($xpath->query('//head/link[@href]') as $link) {
            /** @var \DOMElement $link */
            $rel = $link->getAttribute('rel');
            if (!in_array($rel, $relevantRels)) {
                continue;
            }
            $entry = array_filter([
                'rel'      => $rel,
                'href'     => $link->getAttribute('href'),
                'hreflang' => $link->getAttribute('hreflang') ?: null,
                'type'     => $link->getAttribute('type') ?: null,
                'title'    => $link->getAttribute('title') ?: null,
            ]);
            if ($entry) {
                $links[] = $entry;
            }
        }
        if ($links) {
            $head['links'] = $links;
        }

        $metas = [];
        foreach ($xpath->query('//head/meta') as $meta) {
            /** @var \DOMElement $meta */
            $key = $meta->getAttribute('property') ?: $meta->getAttribute('name');
            $content = $meta->getAttribute('content');
            if ($key && $content) {
                $metas[$key] = $content;
            }
        }
        if ($metas) {
            $head['meta'] = $metas;
        }

        $ldJsonItems = [];
        foreach ($xpath->query('//head/script[@type="application/ld+json"]') as $script) {
            $parsed = json_decode(trim($script->textContent), true);
            if ($parsed) {
                $ldJsonItems[] = $parsed;
            }
        }
        if ($ldJsonItems) {
            $head['ld+json'] = $ldJsonItems;
        }

        return $head;
    }

    private function parseElement(\DOMNode $node): mixed
    {
        if (!($node instanceof \DOMElement)) {
            return null;
        }

        $tagName = strtolower($node->nodeName);

        if ($tagName === 'iframe') {
            $result = array_filter([
                'src' => $node->getAttribute('src'),
                'title' => $node->getAttribute('title'),
            ]);
            return $result ?: null;
        }

        if ($tagName === 'picture') {
            foreach ($node->childNodes as $child) {
                if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'img') {
                    return $this->parseElement($child);
                }
            }
            return null;
        }

        if ($tagName === 'img') {
            $src = $node->getAttribute('src') ?: $node->getAttribute('data-src');
            if (!$src) {
                return null;
            }
            $result = ['src' => $src];
            foreach (['alt', 'title', 'width', 'height', 'loading', 'srcset'] as $attr) {
                if ($value = $node->getAttribute($attr)) {
                    $result[$attr] = $value;
                }
            }
            return $result;
        }

        if ($tagName === 'a') {
            $result = [];
            if ($href = $node->getAttribute('href')) {
                $result['href'] = $href;
            }
            foreach (['rel', 'target', 'title'] as $attr) {
                if ($value = $node->getAttribute($attr)) {
                    $result[$attr] = $value;
                }
            }
            if ($text = trim($node->textContent)) {
                $result['text'] = $text;
            }
            return $result ?: null;
        }

        $childResults = [];
        $textParts = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = trim($child->nodeValue ?? '');
                if ($text !== '') {
                    $textParts[] = $text;
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE && $child instanceof \DOMElement) {
                $childTag = strtolower($child->nodeName);

                if (in_array($childTag, ['style', 'noscript'])) {
                    continue;
                }

                if ($childTag === 'script') {
                    if ($child->getAttribute('type') === 'application/ld+json') {
                        $parsed = json_decode(trim($child->textContent), true);
                        if ($parsed) {
                            $childResults[] = ['ld+json', $parsed];
                        }
                    }
                    continue;
                }

                $childResult = $this->parseElement($child);
                if ($childResult !== null) {
                    $childResults[] = [$childTag, $childResult];
                }
            }
        }

        // Leaf node — return its text content
        if (empty($childResults)) {
            $text = implode(' ', $textParts);
            return $text !== '' ? $text : null;
        }

        // Count tag occurrences so duplicates become arrays
        $tagCounts = [];
        foreach ($childResults as [$tag]) {
            $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
        }

        $result = [];
        if ($textParts) {
            $result['_text'] = implode(' ', $textParts);
        }

        foreach ($childResults as [$tag, $value]) {
            if ($tagCounts[$tag] > 1) {
                $result[$tag][] = $value;
            } else {
                $result[$tag] = $value;
            }
        }

        return $result;
    }
}
