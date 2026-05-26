<?php

declare(strict_types=1);

use App\Domain\ContentExtraction\DTOs\PageFetchResult;
use App\Domain\ContentExtraction\Services\PageFetcher;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $user = User::factory()->create();
    $this->website = Website::create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'url' => 'https://example.com',
    ]);
    $this->page = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page',
        'path' => '/page',
        'slug' => 'page',
    ]);
});

it('returns a PageFetchResult with html for a 200 response', function () {
    Http::fake(['*' => Http::response('<html><body>Hello</body></html>', 200)]);

    $result = app(PageFetcher::class)->fetch($this->page);

    expect($result)->toBeInstanceOf(PageFetchResult::class)
        ->and($result->httpStatus)->toBe(200)
        ->and($result->html)->toContain('Hello')
        ->and($result->redirectUrl)->toBeNull()
        ->and($result->isRedirect())->toBeFalse();
});

it('returns a PageFetchResult with redirect info for a 301 response', function () {
    Http::fake(['*' => Http::response('', 301, ['Location' => 'https://example.com/new-page'])]);

    $result = app(PageFetcher::class)->fetch($this->page);

    expect($result)->toBeInstanceOf(PageFetchResult::class)
        ->and($result->httpStatus)->toBe(301)
        ->and($result->html)->toBeNull()
        ->and($result->redirectUrl)->toBe('https://example.com/new-page')
        ->and($result->isRedirect())->toBeTrue();
});

it('returns a PageFetchResult with redirect info for a 302 response', function () {
    Http::fake(['*' => Http::response('', 302, ['Location' => 'https://example.com/temp'])]);

    $result = app(PageFetcher::class)->fetch($this->page);

    expect($result->httpStatus)->toBe(302)
        ->and($result->isRedirect())->toBeTrue()
        ->and($result->redirectUrl)->toBe('https://example.com/temp');
});

it('throws an exception with code 404 for a 404 response', function () {
    Http::fake(['*' => Http::response('Not Found', 404)]);

    app(PageFetcher::class)->fetch($this->page);
})->throws(\Exception::class, '404');

it('throws an exception for a 500 response', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    app(PageFetcher::class)->fetch($this->page);
})->throws(\Exception::class);
