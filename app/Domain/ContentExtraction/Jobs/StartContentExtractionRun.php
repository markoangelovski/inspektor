<?php

namespace App\Domain\ContentExtraction\Jobs;

use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;

class StartContentExtractionRun implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly string $runId
    ) {}

    public function handle(): void
    {
        $run = ContentExtractionRun::find($this->runId);
        if (!$run || $run->status->isTerminal()) return;

        // 1. Bulk Insert Tickets (Faster & avoids row-by-row overhead on Neon)
        // We use 'on conflict do nothing' via insertOrIgnore to prevent duplicates
        $pageIds = Page::where('website_id', $run->website_id)->pluck('id');

        $tickets = $pageIds->map(fn($id) => [
            'id' => (string) str()->ulid(),
            'page_id' => $id,
            'content_extraction_run_id' => $run->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        PageExtraction::insertOrIgnore($tickets);

        // 2. Update Run Stats
        $run->update([
            'status' => ContentExtractionRunStatus::Running,
            'processed_pages' => 0,
            'started_at' => now(),
        ]);

        // 3. Dispatch only for tickets that haven't started yet
        $pendingTickets = PageExtraction::where('content_extraction_run_id', $run->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingTickets as $ticket) {
            // We update the status to 'processing' immediately BEFORE dispatching
            // to prevent another process/retry from dispatching it again.
            $ticket->update(['status' => 'processing']);
            ExtractPageContentJob::dispatch($ticket->id)->onQueue('page-extraction');
        }
    }
}
