<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PagesFetcher
{
    /**
     * Fetch page URLs from a leaf sitemap.
     *
     * If the sitemap is a sitemap index, it is skipped.
     */
    public function fetchFromSitemap(string $sitemapUrl): array
    {
        try {
            $response = Http::timeout(15)->get($sitemapUrl);

            if (! $response->ok()) {
                Log::warning("Failed to fetch sitemap: {$sitemapUrl}");
                return [];
            }

            $xml = simplexml_load_string($response->body());

            if (! $xml) {
                Log::warning("Invalid XML at sitemap: {$sitemapUrl}");
                return [];
            }

            /**
             * Sitemap index → skip
             * <sitemapindex>
             */
            if (isset($xml->sitemap)) {
                return [];
            }

            /**
             * Leaf sitemap → extract URLs
             * <urlset>
             */
            if (! isset($xml->url)) {
                return [];
            }

            $pages = [];

            foreach ($xml->url as $urlNode) {
                $pageUrl = trim((string) $urlNode->loc);

                if ($pageUrl !== '') {
                    $pages[] = $pageUrl;
                }
            }

            return $pages;
        } catch (Throwable $e) {
            // Fail per sitemap, not per job
            Log::error(
                "Error processing sitemap {$sitemapUrl}: {$e->getMessage()}",
                ['exception' => $e]
            );

            return [];
        }
    }
}
