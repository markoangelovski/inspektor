<?php

namespace App\Jobs;

use App\Actions\Websites\TestSitemapsForChangesAction;
use App\Models\Website;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class TestSitemapsForChangesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $websiteId) {}

    public function handle(TestSitemapsForChangesAction $action): void
    {
        $website = Website::find($this->websiteId);

        if (! $website) {
            Cache::forget(static::pendingKey($this->websiteId));

            return;
        }

        $sitemapResult = $action->execute($website);

        // Persist intermediate data for the pages job (short TTL — just needs to
        // survive until TestPagesForChangesJob is picked up by a worker).
        Cache::put(static::sitemapDataKey($this->websiteId), $sitemapResult, now()->addMinutes(15));

        TestPagesForChangesJob::dispatch($this->websiteId);
    }

    public function failed(\Throwable $e): void
    {
        Cache::put(
            static::resultKey($this->websiteId),
            [
                'hasChanges' => false,
                'message' => '',
                'details' => [],
                'error' => 'Sitemap test failed: '.$e->getMessage(),
            ],
            now()->addHour()
        );
        Cache::forget(static::pendingKey($this->websiteId));
        Cache::forget(static::sitemapDataKey($this->websiteId));
    }

    // ── Cache key helpers (used by TestPagesForChangesJob and StatusCard) ──────

    public static function resultKey(string $websiteId): string
    {
        return "website.{$websiteId}.test_result";
    }

    public static function pendingKey(string $websiteId): string
    {
        return "website.{$websiteId}.test_pending";
    }

    public static function sitemapDataKey(string $websiteId): string
    {
        return "website.{$websiteId}.test_sitemap_data";
    }
}
