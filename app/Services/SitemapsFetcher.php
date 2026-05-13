<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SitemapsFetcher
{
    public function fetch(string $url): array
    {
        $baseUrl = rtrim($url, '/');

        // 1. Fetch robots.txt
        $robotsUrl = $baseUrl.'/robots.txt';
        $robotsResponse = Http::timeout(10)->get($robotsUrl);

        if (! $robotsResponse->ok()) {
            throw new \Exception('robots.txt not found');
        }

        // 2. Parse robots.txt for sitemap URLs
        $sitemapUrls = $this->extractSitemapsFromRobots($robotsResponse->body());

        if (empty($sitemapUrls)) {
            $sitemapUrls = [$baseUrl.'/sitemap.xml'];
        }

        // 3. Recursively fetch all sitemaps (keyed by URL for deduplication)
        $collected = [];
        foreach ($sitemapUrls as $sitemapUrl) {
            $this->fetchSitemapRecursive($sitemapUrl, $collected, null);
        }

        // 4. Return as plain array of ['url', 'lastmod'] entries
        return array_values($collected);
    }

    protected function extractSitemapsFromRobots(string $robotsTxt): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $robotsTxt);

        return collect($lines)
            ->filter(fn ($line) => Str::startsWith(
                Str::lower(trim($line)),
                'sitemap:'
            ))
            ->map(fn ($line) => trim(substr($line, 8)))
            ->filter()
            ->values()
            ->toArray();
    }

    protected function fetchSitemapRecursive(string $sitemapUrl, array &$collected, ?string $lastmod): void
    {
        if (isset($collected[$sitemapUrl])) {
            return;
        }

        try {
            $response = Http::timeout(15)->get($sitemapUrl);

            if (! $response->ok()) {
                Log::warning("Sitemap fetch failed: {$sitemapUrl} (HTTP {$response->status()})");

                return;
            }

            $collected[$sitemapUrl] = ['url' => $sitemapUrl, 'lastmod' => $lastmod];

            try {
                $xml = simplexml_load_string(trim($response->body()));
            } catch (\Throwable $e) {
                Log::warning("Invalid XML at {$sitemapUrl}: {$e->getMessage()}");

                return;
            }

            if (! $xml) {
                Log::warning("Failed to parse XML at {$sitemapUrl}");

                return;
            }

            // Sitemap index → recurse into children, passing their lastmod
            if (isset($xml->sitemap)) {
                foreach ($xml->sitemap as $sitemap) {
                    $childUrl = trim((string) $sitemap->loc);
                    $childLastmod = isset($sitemap->lastmod) ? (trim((string) $sitemap->lastmod) ?: null) : null;
                    if ($childUrl) {
                        $this->fetchSitemapRecursive($childUrl, $collected, $childLastmod);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error fetching sitemap {$sitemapUrl}: {$e->getMessage()}");
        }
    }
}
