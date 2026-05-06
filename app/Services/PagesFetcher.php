<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PagesFetcher
{
    /**
     * Fetch page entries from a leaf sitemap.
     * Returns array of ['url' => string, 'lastmod' => ?string].
     * Sitemap index files are skipped.
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

            // Sitemap index → skip
            if (isset($xml->sitemap)) {
                return [];
            }

            // Leaf sitemap → extract URLs with lastmod
            if (! isset($xml->url)) {
                return [];
            }

            $pages = [];

            foreach ($xml->url as $urlNode) {
                $pageUrl = trim((string) $urlNode->loc);

                if ($pageUrl === '') {
                    continue;
                }

                $lastmod = isset($urlNode->lastmod) ? (trim((string) $urlNode->lastmod) ?: null) : null;

                $pages[] = ['url' => $pageUrl, 'lastmod' => $lastmod];
            }

            return $pages;
        } catch (Throwable $e) {
            Log::error(
                "Error processing sitemap {$sitemapUrl}: {$e->getMessage()}",
                ['exception' => $e]
            );

            return [];
        }
    }
}
