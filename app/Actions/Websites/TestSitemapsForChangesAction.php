<?php

namespace App\Actions\Websites;

use App\Models\Website;
use App\Services\SitemapsFetcher;
use Carbon\Carbon;

class TestSitemapsForChangesAction
{
    public function __construct(
        private SitemapsFetcher $sitemapsFetcher,
    ) {}

    /**
     * Returns sitemap diff details plus the fresh sitemap URLs for the pages job.
     *
     * @return array{details: array, hasChanges: bool, freshSitemapUrls: string[]}
     */
    public function execute(Website $website): array
    {
        $details = [];
        $hasChanges = false;

        $freshSitemaps = collect($this->sitemapsFetcher->fetch($website->url));
        $storedSitemaps = $website->sitemaps()->get()->keyBy('url');

        $freshUrls = $freshSitemaps->pluck('url');
        $storedUrls = $storedSitemaps->keys();

        // URL-level diff (store the actual URLs — these are always small in number)
        $newSitemapItems = $freshUrls->diff($storedUrls)->values()
            ->map(fn ($url) => ['url' => $url])
            ->toArray();

        $removedSitemapItems = $storedUrls->diff($freshUrls)->values()
            ->map(fn ($url) => ['url' => $url])
            ->toArray();

        if (! empty($newSitemapItems)) {
            $hasChanges = true;
            $count = count($newSitemapItems);
            $details[] = ['label' => "{$count} new sitemap URL(s) found.", 'items' => $newSitemapItems];
        }

        if (! empty($removedSitemapItems)) {
            $hasChanges = true;
            $count = count($removedSitemapItems);
            $details[] = ['label' => "{$count} sitemap URL(s) were removed.", 'items' => $removedSitemapItems];
        }

        // Lastmod diff — only meaningful when at least some sitemaps carry a lastmod
        $noLastmodItems = [];
        $changedItems = [];
        $hasLastmod = false;

        foreach ($freshSitemaps as $fresh) {
            $stored = $storedSitemaps->get($fresh['url']);
            if (! $stored) {
                continue;
            }

            if (! $fresh['lastmod']) {
                $noLastmodItems[] = ['url' => $fresh['url']];

                continue;
            }

            $hasLastmod = true;
            $freshDate = Carbon::parse($fresh['lastmod']);
            if ($stored->lastmod && $freshDate->ne($stored->lastmod)) {
                $changedItems[] = [
                    'url' => $fresh['url'],
                    'old_lastmod' => $stored->lastmod->format('M j, Y'),
                    'new_lastmod' => $freshDate->format('M j, Y'),
                ];
                $hasChanges = true;
            }
        }

        if (! $hasLastmod && ! empty($noLastmodItems)) {
            // No sitemaps use lastmod at all — single note, no URL list
            $details[] = ['label' => 'Sitemaps do not use lastmod dates — cannot determine if any have changed.', 'items' => []];
        } else {
            if (! empty($changedItems)) {
                $count = count($changedItems);
                $details[] = ['label' => "{$count} sitemap(s) have updated lastmod dates.", 'items' => $changedItems];
            }

            if (! empty($noLastmodItems)) {
                // Some (not all) sitemaps lack lastmod — show their URLs
                $count = count($noLastmodItems);
                $details[] = ['label' => "{$count} sitemap(s) have no lastmod date.", 'items' => $noLastmodItems];
            }
        }

        return [
            'details' => $details,
            'hasChanges' => $hasChanges,
            'freshSitemapUrls' => $freshUrls->toArray(),
        ];
    }
}
