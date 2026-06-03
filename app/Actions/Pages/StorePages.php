<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Support\Str;

class StorePages
{
    public function execute(Website $website, array $pages): int
    {
        if (empty($pages)) {
            return 0;
        }

        $rows = [];

        $now = now();

        foreach ($pages as $page) {
            $url = trim($page['url']);
            $lastmod = $page['lastmod'] ?? null;

            if ($url === '') {
                continue;
            }

            // Normalize path & slug
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $path = '/'.ltrim($path, '/'); // ensure leading slash
            $slug = basename($path);

            // Determine parent_path
            if ($path === '/') {
                $parentPath = null;
            } else {
                // Remove trailing slash for processing, we'll add it back if needed
                $hasTrailingSlash = str_ends_with($path, '/');
                $trimmedPath = rtrim($path, '/');

                // Get parent directory
                $parentPath = '/'.ltrim(dirname($trimmedPath), '/');

                // Keep trailing slash if original path had it (except for root '/')
                if ($parentPath !== '/' && $hasTrailingSlash) {
                    $parentPath .= '/';
                }

                // Root level pages should have parentPath as '/'
                if ($parentPath === '') {
                    $parentPath = '/';
                }
            }

            $rows[] = [
                'id' => strtolower(Str::ulid()),
                'website_id' => $website->id,
                'sitemap_id' => $page['sitemap_id'] ?? null,
                'url' => $url,
                'path' => $path,
                'slug' => $slug,
                'parent_path' => $parentPath,
                'lastmod' => $lastmod,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert; on conflict update lastmod, updated_at, and sitemap_id.
        // sitemap_id must be in the update list so existing pages (created before
        // sitemap tracking was added) get populated on the next Refresh.
        Page::upsert($rows, ['website_id', 'url'], ['sitemap_id', 'lastmod', 'updated_at']);

        return count($rows);
    }
}
