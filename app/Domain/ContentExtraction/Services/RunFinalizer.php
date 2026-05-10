<?php

namespace App\Domain\ContentExtraction\Services;

use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;

class RunFinalizer
{
    public function __construct(
        private ExtractionEventStore $eventStore,
    ) {}

    public function checkAndFinalize(ContentExtractionRun $run): void
    {
        $run->refresh();

        if ($run->status->isTerminal()) {
            return;
        }

        $hasUnfinished = $run->pageExtractions()
            ->whereNotIn('status', [PageExtractionStatus::Done, PageExtractionStatus::Failed])
            ->exists();

        if ($hasUnfinished) {
            return;
        }

        $hasErrors = $run->pageExtractions()->where('status', PageExtractionStatus::Failed)->exists();
        $finalStatus = $hasErrors
            ? ContentExtractionRunStatus::CompletedWithErrors
            : ContentExtractionRunStatus::Completed;

        // Pull events BEFORE the atomic update so we have them ready,
        // but only persist them if this worker wins the race ($affected > 0).
        $events = $this->eventStore->pull($run);

        /**
         * ATOMIC UPDATE: distributed lock — only one worker gets $affected = 1.
         */
        $affected = ContentExtractionRun::where('id', $run->id)
            ->where('status', ContentExtractionRunStatus::Running)
            ->update([
                'status'          => $finalStatus,
                'finished_at'     => now(),
                'processed_pages' => $run->pageExtractions()->count(),
                'events'          => json_encode($events ?: []),
            ]);

        // if ($affected > 0) {
        //     $this->cleanupOldRuns($run);
        // }
    }

    private function cleanupOldRuns(ContentExtractionRun $run): void
    {
        // Only delete other terminal runs — never touch pending/running/paused ones,
        // which may have been created while this run was finalizing.
        $idsToDelete = ContentExtractionRun::where('website_id', $run->website_id)
            ->where('id', '!=', $run->id)
            ->whereIn('status', ['completed', 'completed_with_errors', 'failed'])
            ->orderByDesc('created_at')
            ->take(100)
            ->pluck('id');

        if ($idsToDelete->isEmpty()) {
            return;
        }

        ContentExtractionRun::whereIn('id', $idsToDelete)->delete();
    }
}
