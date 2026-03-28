<?php

namespace App\Livewire\ContentExtraction;

use App\Models\Website;
use Livewire\Component;
use App\Domain\ContentExtraction\Services\ToggleRunState;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Actions\CreateContentExtractionRunAction;

class StatusCard extends Component
{
    public Website $website;
    public ?string $runId = null;
    public string|ContentExtractionRunStatus $status = 'idle';
    public int $processed = 0;
    public int $total = 0;
    public ?string $lastSynced = 'Never';

    public function mount(Website $website)
    {
        $this->website = $website;
        $this->loadLatestRun();
    }

    public function loadLatestRun(): void
    {
        $run = ContentExtractionRun::where('website_id', $this->website->id)
            ->latest()
            ->first();

        if (! $run) return;

        $this->runId = $run->id;
        $this->status = $run->status;
        $this->processed = $run->processed_pages;
        $this->total = $run->total_pages;
        $this->lastSynced = $run->finished_at
            ? $run->finished_at->diffForHumans()
            : ($run->started_at ? 'In progress...' : 'Never');
    }

    public function refresh()
    {
        if (!$this->runId || in_array($this->status, ['completed', 'failed', 'cancelled'])) {
            return;
        }

        $this->loadLatestRun();
    }

    public function start(CreateContentExtractionRunAction $action): void
    {
        $run = $action->execute($this->website);

        $this->runId = $run->id;
        $this->status = $run->status;

        // 🔥 THIS IS THE KEY PART
        $this->dispatch('content-extraction.started', runId: $run->id);
    }

    public function pause()
    {
        $run = ContentExtractionRun::find($this->runId);
        if ($run) {
            app(ToggleRunState::class)->pause($run);
            $this->loadLatestRun();
        }
    }

    public function resume()
    {
        $run = ContentExtractionRun::find($this->runId);
        if ($run) {
            app(ToggleRunState::class)->resume($run);
            $this->loadLatestRun();
        }
    }

    public function restart(): void
    {
        $this->start(
            app(CreateContentExtractionRunAction::class)
        );
    }


    public function render()
    {
        return view('livewire.content-extraction.status-card');
    }
}
