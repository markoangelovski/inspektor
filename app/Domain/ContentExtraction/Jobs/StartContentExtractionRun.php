<?php

namespace App\Domain\ContentExtraction\Jobs;

use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Services\RunFinalizer;
use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StartContentExtractionRun implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly string $runId
    ) {}

    public function handle(RunFinalizer $finalizer): void
    {
        $run = ContentExtractionRun::find($this->runId);
        if (! $run || $run->status->isTerminal()) {
            return;
        }

        // 1. Bulk Insert Tickets — pre-skip pages with a known non-200 status
        $pages = Page::where('website_id', $run->website_id)
            ->select(['id', 'http_status'])
            ->get();

        $tickets = $pages->map(fn (Page $page) => [
            'id' => (string) str()->ulid(),
            'page_id' => $page->id,
            'website_id' => $run->website_id,
            'content_extraction_run_id' => $run->id,
            'status' => ($page->http_status !== null && $page->http_status !== 200) ? 'skipped' : 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        PageExtraction::insertOrIgnore($tickets);

        $skippedCount = collect($tickets)->where('status', 'skipped')->count();

        // 2. Update Run Stats
        $run->update([
            'status' => ContentExtractionRunStatus::Running,
            'total_pages' => count($tickets),
            'processed_pages' => $skippedCount,
            'started_at' => now(),
        ]);

        // 3. Dispatch only for tickets that haven't started yet
        $pendingTickets = PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingTickets->isEmpty()) {
            $finalizer->checkAndFinalize($run);

            return;
        }

        foreach ($pendingTickets as $ticket) {
            // Update to 'processing' BEFORE dispatching to prevent duplicate dispatch
            $ticket->update(['status' => 'processing']);
            ExtractPageContentJob::dispatch($ticket->id)->onQueue('page-extraction');
        }
    }
}
