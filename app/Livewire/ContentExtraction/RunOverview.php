<?php

namespace App\Livewire\ContentExtraction;

use App\Models\Website;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Services\ExtractionEventStore;
use Livewire\Component;

class RunOverview extends Component
{
    public Website $website;
    public ?string $runId = null;

    protected $listeners = [
        'content-extraction.started' => 'onRunStarted',
    ];

    public function mount(Website $website)
    {
        $this->website = $website;
        $this->loadLatestRun();
    }

    public function onRunStarted(string $runId): void
    {
        $this->runId = $runId;
    }

    public function loadLatestRun()
    {
        $run = ContentExtractionRun::where('website_id', $this->website->id)
            ->latest()
            ->first();

        $this->runId = $run?->id;
    }

    public function render(ExtractionEventStore $eventStore)
    {
        $run = $this->runId ? ContentExtractionRun::with(['pageExtractions.page'])->find($this->runId) : null;

        $events = $run ? $eventStore->all($run) : [];

        // Calculate percentage for progress bar
        $progress = 0;
        if ($run && $run->total_pages > 0) {
            $progress = round(($run->processed_pages / $run->total_pages) * 100);
        }

        $isProcessing = $run && in_array(
            $run->status->value,
            ['running', 'pending', 'cancelling'],
            true
        );

        return view('livewire.content-extraction.run-overview', [
            'run' => $run,
            'events' => array_reverse($events),
            'progress' => $progress,
            'isProcessing' => $isProcessing,
        ]);
    }
}
