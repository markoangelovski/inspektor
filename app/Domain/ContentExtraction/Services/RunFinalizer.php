<?php

namespace App\Domain\ContentExtraction\Services;

use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRunEvent;

class RunFinalizer
{
    public function __construct(
        private ExtractionEventStore $eventStore,
    ) {}

    /**
     * Check if all pages are processed, if so, mark run as completed.
     */
    public function checkAndFinalize(ContentExtractionRun $run): void
    {
        // 1. Refresh to get the most recent 'processed_pages' from other workers
        $run->refresh();

        // 2. Early exit if already terminal
        if ($run->status->isTerminal()) {
            return;
        }

        // 3. Robust completion check:
        // We check the DB for any tickets that haven't reached a terminal state yet.
        $hasUnfinished = $run->pageExtractions()
            ->whereNotIn('status', [PageExtractionStatus::Done, PageExtractionStatus::Failed])
            ->exists();

        if ($hasUnfinished) {
            return;
        }

        // 4. Determine final status based on individual ticket successes
        $hasErrors = $run->pageExtractions()->where('status', PageExtractionStatus::Failed)->exists();
        $finalStatus = $hasErrors
            ? ContentExtractionRunStatus::CompletedWithErrors
            : ContentExtractionRunStatus::Completed;

        /**
         * ATOMIC UPDATE: This is our distributed lock.
         * Only one worker will get an $affected value of 1.
         */
        $affected = ContentExtractionRun::where('id', $run->id)
            ->where('status', ContentExtractionRunStatus::Running) // Must still be running
            ->update([
                'status' => $finalStatus,
                'finished_at' => now(),
                // Sync the counter one last time in case of race conditions during increments
                'processed_pages' => $run->pageExtractions()->count(),
            ]);

        // 5. Only the worker that successfully transitioned the state handles the wrap-up
        if ($affected > 0) {
            $this->persistEvents($run);
            $this->cleanupOldRuns($run);
        }
    }

    private function persistEvents(ContentExtractionRun $run): void
    {
        // Fetch all events from Redis.
        $events = $this->eventStore->pull($run);

        if (empty($events)) {
            return;
        }

        // Prepare for bulk insert to Neon (PostgreSQL)
        $rows = array_map(function ($event) use ($run) {
            return [
                'id' => (string) str()->ulid(),
                'website_id' => $run->website_id,
                'content_extraction_run_id' => $run->id,
                'type' => $event['type'] ?? 'page.status',
                'payload' => json_encode($event),
                'created_at' => now(),
            ];
        }, $events);

        // Atomic bulk insert is much faster and safer than a loop of creates
        ContentExtractionRunEvent::insert($rows);
    }

    /**
     * Keep only the last N runs per website.
     * Best-effort cleanup.
     */
    private function cleanupOldRuns(ContentExtractionRun $run): void
    {
        $idsToDelete = ContentExtractionRun::where('website_id', $run->website_id)
            ->orderByDesc('created_at')
            ->skip(1) // keep last 1 run
            ->take(100)
            ->pluck('id');

        if ($idsToDelete->isEmpty()) {
            return;
        }

        ContentExtractionRun::whereIn('id', $idsToDelete)->delete();
        // FK cascade removes related data
    }
}
