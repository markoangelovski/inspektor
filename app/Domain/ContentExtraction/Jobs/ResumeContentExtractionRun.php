<?php

namespace App\Domain\ContentExtraction\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;

class ResumeContentExtractionRun implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly string $runId
    ) {}

    public function handle(): void
    {
        $run = ContentExtractionRun::find($this->runId);
        if (!$run || $run->status->isTerminal()) return;

        // Tickets left in 'processing' when the run was paused had their jobs
        // abandoned mid-flight. Reset them so they can be re-dispatched.
        PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'processing')
            ->update(['status' => 'pending', 'updated_at' => now()]);

        $pendingTickets = PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingTickets as $ticket) {
            $ticket->update(['status' => 'processing']);
            ExtractPageContentJob::dispatch($ticket->id)->onQueue('page-extraction');
        }
    }
}
