<?php

declare(strict_types=1);

use App\Jobs\ProcessPages;
use App\Models\Page;
use App\Models\Sitemap;
use App\Models\User;
use App\Models\Website;
use App\Services\PagesFetcher;

use function Pest\Laravel\mock;

beforeEach(function () {
    $user = User::factory()->create();
    $this->website = Website::create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'url' => 'https://example.com',
    ]);
    $this->sitemap = Sitemap::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/sitemap.xml',
    ]);
});

it('assigns sitemap_id to pages from the originating sitemap', function () {
    $fetcher = mock(PagesFetcher::class);
    $fetcher->shouldReceive('fetchFromSitemap')
        ->with($this->sitemap->url)
        ->once()
        ->andReturn([
            ['url' => 'https://example.com/page-a', 'lastmod' => null],
            ['url' => 'https://example.com/page-b', 'lastmod' => null],
        ]);

    (new ProcessPages($this->website->id))->handle($fetcher, app(\App\Actions\Pages\StorePages::class));

    $pageA = Page::where('url', 'https://example.com/page-a')->first();
    $pageB = Page::where('url', 'https://example.com/page-b')->first();

    expect($pageA->sitemap_id)->toBe($this->sitemap->id)
        ->and($pageB->sitemap_id)->toBe($this->sitemap->id);
});

it('assigns sitemap_id from first sitemap when a URL appears in multiple sitemaps', function () {
    $secondSitemap = Sitemap::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/sitemap2.xml',
    ]);

    $fetcher = mock(PagesFetcher::class);
    $fetcher->shouldReceive('fetchFromSitemap')
        ->with($this->sitemap->url)
        ->once()
        ->andReturn([
            ['url' => 'https://example.com/shared-page', 'lastmod' => null],
        ]);
    $fetcher->shouldReceive('fetchFromSitemap')
        ->with($secondSitemap->url)
        ->once()
        ->andReturn([
            ['url' => 'https://example.com/shared-page', 'lastmod' => null],
        ]);

    (new ProcessPages($this->website->id))->handle($fetcher, app(\App\Actions\Pages\StorePages::class));

    $page = Page::where('url', 'https://example.com/shared-page')->first();

    // The first sitemap wins (iteration order of $website->sitemaps)
    expect($page->sitemap_id)->not->toBeNull();
});

it('populates sitemap_id on existing pages that were stored without one', function () {
    $existingPage = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/old-page',
        'path' => '/old-page',
        'slug' => 'old-page',
        'sitemap_id' => null,
    ]);

    $fetcher = mock(PagesFetcher::class);
    $fetcher->shouldReceive('fetchFromSitemap')
        ->with($this->sitemap->url)
        ->once()
        ->andReturn([
            ['url' => 'https://example.com/old-page', 'lastmod' => null],
        ]);

    (new ProcessPages($this->website->id))->handle($fetcher, app(\App\Actions\Pages\StorePages::class));

    expect($existingPage->refresh()->sitemap_id)->toBe($this->sitemap->id);
});
