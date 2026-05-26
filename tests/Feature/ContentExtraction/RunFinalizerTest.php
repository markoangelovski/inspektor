<?php

declare(strict_types=1);

use App\Domain\ContentExtraction\Enums\ContentExtractionRunStatus;
use App\Domain\ContentExtraction\Enums\PageExtractionStatus;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Models\PageExtraction;
use App\Domain\ContentExtraction\Services\RunFinalizer;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;

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
        'total_pages' => 2,
        'processed_pages' => 0,
    ]);
    $this->page = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page',
        'path' => '/page',
        'slug' => 'page',
    ]);
});

it('completes the run when all tickets are skipped', function () {
    PageExtraction::create([
        'page_id' => $this->page->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Skipped,
        'finished_at' => now(),
    ]);

    $page2 = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page2',
        'path' => '/page2',
        'slug' => 'page2',
    ]);

    PageExtraction::create([
        'page_id' => $page2->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Skipped,
        'finished_at' => now(),
    ]);

    app(RunFinalizer::class)->checkAndFinalize($this->run);

    $this->run->refresh();
    expect($this->run->status)->toBe(ContentExtractionRunStatus::Completed);
});

it('does not complete run when skipped and pending tickets coexist', function () {
    PageExtraction::create([
        'page_id' => $this->page->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Skipped,
        'finished_at' => now(),
    ]);

    $page2 = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page2',
        'path' => '/page2',
        'slug' => 'page2',
    ]);

    PageExtraction::create([
        'page_id' => $page2->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Pending,
    ]);

    app(RunFinalizer::class)->checkAndFinalize($this->run);

    $this->run->refresh();
    expect($this->run->status)->toBe(ContentExtractionRunStatus::Running);
});

it('completes the run when tickets are a mix of done, failed, and skipped', function () {
    $page2 = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page2',
        'path' => '/page2',
        'slug' => 'page2',
    ]);
    $page3 = Page::create([
        'website_id' => $this->website->id,
        'url' => 'https://example.com/page3',
        'path' => '/page3',
        'slug' => 'page3',
    ]);

    PageExtraction::create([
        'page_id' => $this->page->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Done,
        'finished_at' => now(),
    ]);
    PageExtraction::create([
        'page_id' => $page2->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Failed,
        'finished_at' => now(),
    ]);
    PageExtraction::create([
        'page_id' => $page3->id,
        'website_id' => $this->website->id,
        'content_extraction_run_id' => $this->run->id,
        'status' => PageExtractionStatus::Skipped,
        'finished_at' => now(),
    ]);

    app(RunFinalizer::class)->checkAndFinalize($this->run);

    $this->run->refresh();
    expect($this->run->status)->toBe(ContentExtractionRunStatus::CompletedWithErrors);
});
