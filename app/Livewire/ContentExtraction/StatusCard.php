<?php

namespace App\Livewire\ContentExtraction;

use App\Domain\ContentExtraction\Actions\CreateContentExtractionRunAction;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Services\ToggleRunState;
use App\Jobs\TestSitemapsForChangesJob;
use App\Models\Website;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StatusCard extends Component
{
    public Website $website;

    public ?string $runId = null;

    public string|ContentExtractionRunStatus $status = 'idle';

    public int $processed = 0;

    public int $total = 0;

    public ?string $lastSynced = 'Never';

    public string $testStatus = 'idle'; // idle | pending | done

    public ?int $testDispatchedAt = null;

    public ?array $testResult = null;

    public function mount(Website $website): void
    {
        $this->website = $website;
        $this->loadLatestRun();
        $this->restoreTestState();
    }

    public function loadLatestRun(): void
    {
        $run = ContentExtractionRun::where('website_id', $this->website->id)
            ->latest()
            ->first();

        if (! $run) {
            return;
        }

        $this->runId = $run->id;
        $this->status = $run->status;
        $this->processed = $run->processed_pages;
        $this->total = $run->total_pages;
        $this->lastSynced = $run->finished_at
            ? $run->finished_at->diffForHumans()
            : ($run->started_at ? 'In progress...' : 'Never');
    }

    public function refresh(): void
    {
        if (! $this->runId || in_array($this->status, ['completed', 'failed', 'cancelled'])) {
            return;
        }

        $this->loadLatestRun();
    }

    public function testForChanges(): void
    {
        Cache::forget(TestSitemapsForChangesJob::resultKey($this->website->id));
        Cache::forget(TestSitemapsForChangesJob::sitemapDataKey($this->website->id));

        $dispatchedAt = now()->timestamp;
        Cache::put(
            TestSitemapsForChangesJob::pendingKey($this->website->id),
            $dispatchedAt,
            now()->addMinutes(15)
        );

        $this->testResult = null;
        $this->testStatus = 'pending';
        $this->testDispatchedAt = $dispatchedAt;

        TestSitemapsForChangesJob::dispatch($this->website->id);
    }

    public function pollTestResult(): void
    {
        if ($this->testStatus !== 'pending') {
            return;
        }

        $result = Cache::get(TestSitemapsForChangesJob::resultKey($this->website->id));
        if ($result !== null) {
            $this->testResult = $result;
            $this->testStatus = 'done';

            return;
        }

        // Pending marker expired without a result — both jobs died without calling failed()
        if (! Cache::has(TestSitemapsForChangesJob::pendingKey($this->website->id))) {
            $this->testResult = [
                'hasChanges' => false,
                'message' => '',
                'details' => [],
                'error' => 'Test failed unexpectedly. Please try again.',
            ];
            $this->testStatus = 'done';
        }
    }

    public function start(CreateContentExtractionRunAction $action): void
    {
        $this->testResult = null;
        $this->testStatus = 'idle';
        $this->testDispatchedAt = null;
        Cache::forget(TestSitemapsForChangesJob::resultKey($this->website->id));
        Cache::forget(TestSitemapsForChangesJob::pendingKey($this->website->id));
        Cache::forget(TestSitemapsForChangesJob::sitemapDataKey($this->website->id));

        $run = $action->execute($this->website);

        $this->runId = $run->id;
        $this->status = $run->status;

        $this->dispatch('content-extraction.started', runId: $run->id);
    }

    public function pause(): void
    {
        $run = ContentExtractionRun::find($this->runId);
        if ($run) {
            app(ToggleRunState::class)->pause($run);
            $this->loadLatestRun();
        }
    }

    public function resume(): void
    {
        $run = ContentExtractionRun::find($this->runId);
        if ($run) {
            app(ToggleRunState::class)->resume($run);
            $this->loadLatestRun();
        }
    }

    public function restart(): void
    {
        $this->start(app(CreateContentExtractionRunAction::class));
    }

    private function restoreTestState(): void
    {
        $result = Cache::get(TestSitemapsForChangesJob::resultKey($this->website->id));
        if ($result !== null) {
            $this->testResult = $result;
            $this->testStatus = 'done';

            return;
        }

        $dispatchedAt = Cache::get(TestSitemapsForChangesJob::pendingKey($this->website->id));
        if ($dispatchedAt !== null) {
            $this->testStatus = 'pending';
            $this->testDispatchedAt = $dispatchedAt;
        }
    }

    public function render()
    {
        return view('livewire.content-extraction.status-card');
    }
}
