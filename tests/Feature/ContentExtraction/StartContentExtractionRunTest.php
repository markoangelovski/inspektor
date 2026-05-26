<?php

declare(strict_types=1);

use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Jobs\ExtractPageContentJob;
use App\Domain\ContentExtraction\Jobs\StartContentExtractionRun;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Queue;

/**
 * Helper to invoke StartContentExtractionRun::handle() directly, bypassing the queue.
 * dispatchSync() on ShouldQueue jobs routes to the 'sync' queue connection, which
 * Queue::fake() intercepts — preventing the job from actually running.
 */
function runStartJob(string $runId): void
{
    app()->call([new StartContentExtractionRun($runId), 'handle']);
}

beforeEach(function () {
    $user = User::factory()->create();
    $this->website = Website::create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'url' => 'https://example.com',
    ]);
    $this->run = ContentExtractionRun::create([
        'website_id' => $this->website->id,
        'created_by' => $user->id,
        'status' => ContentExtractionRunStatus::Pending,
        'total_pages' => 0,
        'processed_pages' => 0,
    ]);
});

it('creates pending tickets and dispatches jobs for pages with no http_status', function () {
    Queue::fake();

    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/a', 'path' => '/a', 'slug' => 'a']);
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/b', 'path' => '/b', 'slug' => 'b']);

    runStartJob($this->run->id);

    expect(PageExtraction::where('content_extraction_run_id', $this->run->id)
        ->where('status', 'processing')->count())->toBe(2);

    Queue::assertPushedOn('page-extraction', ExtractPageContentJob::class);
});

it('creates skipped tickets for pages with a known non-200 status', function () {
    Queue::fake();

    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/ok', 'path' => '/ok', 'slug' => 'ok', 'http_status' => 200]);
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/redirect', 'path' => '/redirect', 'slug' => 'redirect', 'http_status' => 301]);
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/gone', 'path' => '/gone', 'slug' => 'gone', 'http_status' => 404]);

    runStartJob($this->run->id);

    expect(PageExtraction::where('content_extraction_run_id', $this->run->id)
        ->where('status', 'skipped')->count())->toBe(2);

    expect(PageExtraction::where('content_extraction_run_id', $this->run->id)
        ->where('status', 'processing')->count())->toBe(1);
});

it('sets total_pages and pre-counts skipped pages in processed_pages', function () {
    Queue::fake();

    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/a', 'path' => '/a', 'slug' => 'a']);
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/b', 'path' => '/b', 'slug' => 'b', 'http_status' => 301]);

    runStartJob($this->run->id);

    $this->run->refresh();
    expect($this->run->total_pages)->toBe(2)
        ->and($this->run->processed_pages)->toBe(1);
});

it('finalizes the run immediately when all pages are pre-skipped', function () {
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/a', 'path' => '/a', 'slug' => 'a', 'http_status' => 301]);
    Page::create(['website_id' => $this->website->id, 'url' => 'https://example.com/b', 'path' => '/b', 'slug' => 'b', 'http_status' => 404]);

    runStartJob($this->run->id);

    $this->run->refresh();
    expect($this->run->status)->toBe(ContentExtractionRunStatus::Completed);
});
