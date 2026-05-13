<?php

namespace App\Domain\ContentExtraction\Jobs;

use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Services\RunFinalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ResumeContentExtractionRun implements ShouldQueue
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

        // Jobs that were in-flight when the run was paused left their tickets in
        // 'processing'. Reset them so they can be re-dispatched below.
        PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'processing')
            ->update(['status' => 'pending', 'updated_at' => now()]);

        $pendingTickets = PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingTickets->isEmpty()) {
            // All tickets finished while the run was paused. No new jobs to dispatch,
            // so the normal finalizer path will never be called — finalize explicitly.
            $finalizer->checkAndFinalize($run);

            return;
        }

        foreach ($pendingTickets as $ticket) {
            $ticket->update(['status' => 'processing']);
            ExtractPageContentJob::dispatch($ticket->id)->onQueue('page-extraction');
        }
    }
}
