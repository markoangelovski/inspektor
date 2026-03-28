<?php

namespace App\Domain\ContentExtraction\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Http;

class PageFetcher
{
    public function fetch(Page $page): string
    {
        // Using a common Chrome User-Agent
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

        $response = Http::withHeaders([
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ])
            ->timeout(30)
            ->retry(2, 100)
            ->get($page->url);

        if ($response->status() === 404) {
            // Throwing a specific message helps the Job catch block categorize it
            throw new \Exception("HTTP request returned status code 404", 404);
        }

        if ($response->failed()) {
            throw new \Exception("Failed to fetch URL: {$page->url} (Status: {$response->status()})");
        }

        return $response->body();
    }
}
