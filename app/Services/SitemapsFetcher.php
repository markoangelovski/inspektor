<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SitemapsFetcher
{
    public function fetch(string $url)
    {
        $baseUrl = rtrim($url, '/');

        // 1. Fetch robots.txt
        $robotsUrl = $baseUrl . '/robots.txt';
        $robotsResponse = Http::timeout(10)->get($robotsUrl);

        if (! $robotsResponse->ok()) {
            throw new \Exception('robots.txt not found');
        }

        // 2. Parse robots.txt for sitemap URLs
        $sitemapUrls = $this->extractSitemapsFromRobots(
            $robotsResponse->body()
        );

        if (empty($sitemapUrls)) {
            throw new \Exception('No sitemap found in robots.txt');
        }

        // 3. Recursively fetch all sitemaps
        $collected = [];
        foreach ($sitemapUrls as $sitemapUrl) {
            $this->fetchSitemapRecursive($sitemapUrl, $collected);
        }

        // 4. Return array of unique URLs
        return array_values(array_unique($collected)); // plain array of strings
    }

    /**
     * Extract sitemap URLs from robots.txt
     */
    protected function extractSitemapsFromRobots(string $robotsTxt): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $robotsTxt);

        return collect($lines)
            ->filter(fn($line) => Str::startsWith(
                Str::lower(trim($line)),
                'sitemap:'
            ))
            ->map(fn($line) => trim(substr($line, 8)))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Recursively fetch sitemap URLs
     */
    protected function fetchSitemapRecursive(string $sitemapUrl, array &$collected): void
    {
        // Skip duplicates
        if (in_array($sitemapUrl, $collected, true)) {
            return;
        }

        try {
            // Fetch sitemap
            $response = Http::timeout(15)->get($sitemapUrl);

            if (! $response->ok()) {
                \Log::warning("Sitemap fetch failed: {$sitemapUrl} (HTTP {$response->status()})");
                return;
            }

            $collected[] = $sitemapUrl;

            // Parse XML
            try {
                $xml = simplexml_load_string($response->body());
            } catch (\Throwable $e) {
                \Log::warning("Invalid XML at {$sitemapUrl}: {$e->getMessage()}");
                return;
            }

            if (! $xml) {
                \Log::warning("Failed to parse XML at {$sitemapUrl}");
                return;
            }

            // 3a. Sitemap index → recurse
            if (isset($xml->sitemap)) {
                foreach ($xml->sitemap as $sitemap) {
                    $childUrl = trim((string) $sitemap->loc);
                    if ($childUrl) {
                        $this->fetchSitemapRecursive($childUrl, $collected);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Catch any unexpected errors per URL
            \Log::error("Error fetching sitemap {$sitemapUrl}: {$e->getMessage()}");
            return;
        }
    }
}
