<?php

namespace App\Livewire\ContentExtraction;

use App\Domain\ContentExtraction\Actions\CreateContentExtractionRunAction;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Services\ExtractionEventStore;
use App\Domain\ContentExtraction\Services\ToggleRunState;
use App\Models\Website;
use Livewire\Component;

class RunOverview extends Component
{
    public Website $website;

    public ?string $selectedRunId = null;

    protected $listeners = [
        'content-extraction.started' => 'onRunStarted',
    ];

    public function mount(Website $website): void
    {
        $this->website = $website;
        $this->loadLatestRunId();
    }

    public function onRunStarted(string $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function loadLatestRunId(): void
    {
        $run = ContentExtractionRun::where('website_id', $this->website->id)
            ->latest()
            ->first();

        $this->selectedRunId = $run?->id;
    }

    public function selectRun(string $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function pause(): void
    {
        $run = ContentExtractionRun::find($this->selectedRunId);
        if ($run) {
            app(ToggleRunState::class)->pause($run);
        }
    }

    public function resume(): void
    {
        $run = ContentExtractionRun::find($this->selectedRunId);
        if ($run) {
            app(ToggleRunState::class)->resume($run);
        }
    }

    public function restart(CreateContentExtractionRunAction $action): void
    {
        $run = $action->execute($this->website);
        $this->selectedRunId = $run->id;
        $this->dispatch('content-extraction.started', runId: $run->id);
    }

    public function render(ExtractionEventStore $eventStore)
    {
        $runs = ContentExtractionRun::where('website_id', $this->website->id)
            ->latest()
            ->get();

        $run = $this->selectedRunId
            ? ContentExtractionRun::with(['pageExtractions.page'])->find($this->selectedRunId)
            : null;

        $events = $run ? array_reverse($eventStore->all($run)) : [];

        $progress = 0;
        if ($run && $run->total_pages > 0) {
            $progress = round(($run->processed_pages / $run->total_pages) * 100);
        }

        $isProcessing = $run && in_array(
            $run->status->value,
            ['running', 'pending', 'cancelling'],
            true
        );

        return view('livewire.content-extraction.run-overview', compact(
            'runs', 'run', 'events', 'progress', 'isProcessing'
        ));
    }
}
