<?php

declare(strict_types=1);

use App\Domain\ContentExtraction\DTOs\PageFetchResult;
use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Enums\PageExtractionFailureType;
use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Jobs\ExtractPageContentJob;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Services\PageFetcher;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;

use function Pest\Laravel\mock;

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
        'status' => ContentExtractionRunStatus::Running,
        'total_pages' => 1,
        'processed_pages' => 0,
    ]);
    $this->page = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/old-page',
        'path' => '/old-page',
        'slug' => 'old-page',
    ]);
    $this->ticket = PageExtraction::create([
        'page_id' => $this->page->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Processing,
        'started_at' => now(),
    ]);
});

it('marks ticket as skipped and stores redirect status when page redirects', function () {
    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andReturn(
        new PageFetchResult(301, null, 'https://example.com/new-page')
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    $this->page->refresh();
    expect($this->page->http_status)->toBe(301)
        ->and($this->page->redirect_url)->toBe('https://example.com/new-page');

    $this->ticket->refresh();
    expect($this->ticket->status)->toBe(PageExtractionStatus::Skipped)
        ->and($this->ticket->failure_type)->toBe(PageExtractionFailureType::Redirect->value)
        ->and($this->ticket->finished_at)->not->toBeNull();
});

it('creates the redirect destination page if it does not exist', function () {
    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andReturn(
        new PageFetchResult(301, null, 'https://example.com/new-page')
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    $destination = Page::where('website_id', $this->website->id)
        ->where('url', 'https://example.com/new-page')
        ->first();

    expect($destination)->not->toBeNull()
        ->and($destination->path)->toBe('/new-page')
        ->and($destination->slug)->toBe('new-page');
});

it('does not create destination page for cross-domain redirects', function () {
    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andReturn(
        new PageFetchResult(301, null, 'https://other-domain.com/page')
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    expect(Page::where('url', 'https://other-domain.com/page')->exists())->toBeFalse();
});

it('stores http_status 200 on the page after successful extraction', function () {
    $html = '<html><head><title>Test</title></head><body><p>Content</p></body></html>';

    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andReturn(
        new PageFetchResult(200, $html, null)
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    $this->page->refresh();
    expect($this->page->http_status)->toBe(200);

    $this->ticket->refresh();
    expect($this->ticket->status)->toBe(PageExtractionStatus::Done);
});

it('stores http_status on the page when extraction fails with a 404', function () {
    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andThrow(
        new \Exception('HTTP request returned status code 404', 404)
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    $this->page->refresh();
    expect($this->page->http_status)->toBe(404);

    $this->ticket->refresh();
    expect($this->ticket->status)->toBe(PageExtractionStatus::Failed)
        ->and($this->ticket->failure_type)->toBe(PageExtractionFailureType::ParsePermanent->value);
});

it('finalizes the run after processing the only page via redirect', function () {
    $fetcher = mock(PageFetcher::class);
    $fetcher->shouldReceive('fetch')->once()->andReturn(
        new PageFetchResult(301, null, 'https://example.com/new-page')
    );

    app()->call([new ExtractPageContentJob($this->ticket->id), 'handle']);

    $this->run->refresh();
    expect($this->run->status)->toBe(ContentExtractionRunStatus::Completed);
});
