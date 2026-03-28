<?php

namespace App\Actions\Sitemaps;

use App\Models\Sitemap;
use App\Models\Website;

class AddSitemap
{
    public function execute(Website $website, string $url): Sitemap
    {
        // Normalize URL
        $url = trim($url);

        // Create or ignore duplicate (unique index protects us)
        $sitemap = Sitemap::firstOrCreate([
            'website_id' => $website->id,
            'url' => $url,
        ]);

        // Keep website metadata consistent
        $website->update([
            'sitemaps_fetched' => true,
            'sitemaps_count' => $website->sitemaps()->count(),
            'sitemaps_last_sync' => now(),
            'sitemaps_message' => 'Sitemap added manually',
        ]);

        return $sitemap;
    }
}
