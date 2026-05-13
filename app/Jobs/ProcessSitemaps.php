<?php

namespace App\Jobs;

use App\Models\Sitemap;
use App\Models\Website;
use App\Services\SitemapsFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ProcessSitemaps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $websiteId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $websiteId)
    {
        $this->websiteId = $websiteId;
    }

    /**
     * Execute the job.
     */
    public function handle(SitemapsFetcher $fetcher): void
    {
        $website = Website::find($this->websiteId);

        // Website might have been deleted
        if ($website === null) {
            return;
        }

        if (! $website->sitemaps_processing) {
            return;
        }

        $sitemaps = $fetcher->fetch($website->url);

        if (empty($sitemaps)) {
            Website::whereKey($this->websiteId)->update([
                'sitemaps_processing' => false,
                'sitemaps_message' => 'no sitemaps',
            ]);

            return;
        }

        $now = now();

        $rows = collect($sitemaps)->map(fn (array $sitemap) => [
            'id' => strtolower(Str::ulid()),
            'website_id' => $website->id,
            'url' => $sitemap['url'],
            'lastmod' => $sitemap['lastmod'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        Sitemap::upsert($rows, ['website_id', 'url'], ['lastmod', 'updated_at']);

        Website::whereKey($this->websiteId)->update([
            'sitemaps_fetched' => true,
            'sitemaps_count' => count($rows),
            'sitemaps_last_sync' => $now,
            'sitemaps_message' => 'ok',
            'sitemaps_processing' => false,
        ]);

        ProcessPages::dispatch($this->websiteId);
    }

    public function failed(Throwable $e): void
    {
        if ($website = Website::find($this->websiteId)) {
            $website->update([
                'sitemaps_processing' => false,
                'sitemaps_last_sync' => now(),
                'sitemaps_message' => $e->getMessage(),
            ]);
        }
    }
}
