<?php

declare(strict_types=1);

use App\Actions\Pages\StorePages;
use App\Models\Page;
use App\Models\Sitemap;
use App\Models\User;
use App\Models\Website;

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
    $this->action = app(StorePages::class);
});

it('stores sitemap_id on new pages', function () {
    $this->action->execute($this->website, [
        ['url' => 'https://example.com/page-a', 'lastmod' => null, 'sitemap_id' => $this->sitemap->id],
    ]);

    $page = Page::where('url', 'https://example.com/page-a')->first();

    expect($page)->not->toBeNull()
        ->and($page->sitemap_id)->toBe($this->sitemap->id);
});

it('updates sitemap_id on existing pages when re-run', function () {
    $otherSitemap = Sitemap::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/sitemap2.xml',
    ]);

    // First insert — assigns original sitemap
    $this->action->execute($this->website, [
        ['url' => 'https://example.com/page-a', 'lastmod' => null, 'sitemap_id' => $this->sitemap->id],
    ]);

    // Second insert — sitemap_id IS updated to the new value
    $this->action->execute($this->website, [
        ['url' => 'https://example.com/page-a', 'lastmod' => null, 'sitemap_id' => $otherSitemap->id],
    ]);

    $page = Page::where('url', 'https://example.com/page-a')->first();

    expect($page->sitemap_id)->toBe($otherSitemap->id);
});

it('populates null sitemap_id on existing pages when re-run', function () {
    // Simulate a page that existed before sitemap tracking
    Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page-b',
        'path' => '/page-b',
        'slug' => 'page-b',
        'sitemap_id' => null,
    ]);

    $this->action->execute($this->website, [
        ['url' => 'https://example.com/page-b', 'lastmod' => null, 'sitemap_id' => $this->sitemap->id],
    ]);

    $page = Page::where('url', 'https://example.com/page-b')->first();

    expect($page->sitemap_id)->toBe($this->sitemap->id);
});

it('stores pages with null sitemap_id when none is provided', function () {
    $this->action->execute($this->website, [
        ['url' => 'https://example.com/page-b', 'lastmod' => null],
    ]);

    $page = Page::where('url', 'https://example.com/page-b')->first();

    expect($page)->not->toBeNull()
        ->and($page->sitemap_id)->toBeNull();
});
