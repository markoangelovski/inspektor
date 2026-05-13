<?php

namespace App\Jobs;

use App\Actions\Pages\StorePages;
use App\Models\Website;
use App\Services\PagesFetcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $websiteId;

    public function __construct(string $websiteId)
    {
        $this->websiteId = $websiteId;
    }

    public function handle(PagesFetcher $pagesFetcher, StorePages $storePages): void
    {
        $website = Website::find($this->websiteId);

        // Website may have been deleted
        if (! $website) {
            return;
        }

        try {
            $allPages = [];

            foreach ($website->sitemaps as $sitemap) {
                $pages = $pagesFetcher->fetchFromSitemap($sitemap->url);
                foreach ($pages as $page) {
                    $allPages[$page['url']] = $page; // deduplicate by URL
                }
            }

            // Bulk store pages
            $storedCount = $storePages->execute($website, array_values($allPages));

            // Update website stats
            $website->update([
                'pages_fetched' => true,
                'pages_count' => $storedCount,
                'pages_last_sync' => now(),
                'pages_processing' => false,
            ]);
        } catch (Throwable $e) {
            report($e);

            Website::whereKey($this->websiteId)->update([
                'pages_processing' => false,
                'pages_message' => $e->getMessage(),
            ]);

            throw $e; // allow failed() to run
        }
    }

    /**
     * Optional: runs when the job has failed permanently
     */
    public function failed(Throwable $e): void
    {
        if ($website = Website::find($this->websiteId)) {
            $website->update([
                'pages_processing' => false,
                'pages_message' => $e->getMessage(),
            ]);
        }
    }
}
