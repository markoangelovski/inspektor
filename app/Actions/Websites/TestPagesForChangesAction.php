<?php

namespace App\Actions\Websites;

use App\Models\Website;
use App\Services\PagesFetcher;
use Carbon\Carbon;

class TestPagesForChangesAction
{
    public function __construct(
        private PagesFetcher $pagesFetcher,
    ) {}

    /**
     * @param  string[]  $freshSitemapUrls  Sitemap URLs collected by the sitemaps job.
     * @return array{details: array, hasChanges: bool}
     */
    public function execute(Website $website, array $freshSitemapUrls): array
    {
        $details = [];
        $hasChanges = false;

        $freshPages = collect();
        foreach ($freshSitemapUrls as $sitemapUrl) {
            $freshPages = $freshPages->concat(
                $this->pagesFetcher->fetchFromSitemap($sitemapUrl)
            );
        }
        $freshPages = $freshPages->keyBy('url');
        $storedPages = $website->pages()->get()->keyBy('url');

        // URL-level diff (store the actual URLs — actionable data)
        $newPageItems = $freshPages->keys()->diff($storedPages->keys())->values()
            ->map(fn ($url) => ['url' => $url])
            ->toArray();

        if (! empty($newPageItems)) {
            $hasChanges = true;
            $count = count($newPageItems);
            $details[] = ['label' => "{$count} new page(s) found in the sitemaps.", 'items' => $newPageItems];
        }

        // Lastmod diff — only meaningful when at least some pages carry a lastmod
        $noLastmodItems = [];
        $updatedItems = [];
        $hasLastmod = false;

        foreach ($freshPages as $url => $fresh) {
            $stored = $storedPages->get($url);
            if (! $stored) {
                continue;
            }

            if (! $fresh['lastmod']) {
                $noLastmodItems[] = ['url' => $url];

                continue;
            }

            $hasLastmod = true;
            $freshDate = Carbon::parse($fresh['lastmod']);
            if ($stored->lastmod && $freshDate->ne($stored->lastmod)) {
                $updatedItems[] = [
                    'url' => $url,
                    'old_lastmod' => $stored->lastmod->format('M j, Y'),
                    'new_lastmod' => $freshDate->format('M j, Y'),
                ];
                $hasChanges = true;
            }
        }

        if (! $hasLastmod && ! empty($noLastmodItems)) {
            // No pages use lastmod at all — single note, no URL list
            $details[] = ['label' => 'Pages do not use lastmod dates — cannot determine if any have changed.', 'items' => []];
        } else {
            if (! empty($updatedItems)) {
                $count = count($updatedItems);
                $details[] = ['label' => "{$count} page(s) have updated lastmod dates.", 'items' => $updatedItems];
            }

            if (! empty($noLastmodItems)) {
                // Some (not all) pages lack lastmod — show their URLs
                $count = count($noLastmodItems);
                $details[] = ['label' => "{$count} page(s) have no lastmod date.", 'items' => $noLastmodItems];
            }
        }

        return [
            'details' => $details,
            'hasChanges' => $hasChanges,
        ];
    }
}
