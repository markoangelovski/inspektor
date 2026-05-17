<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Pages
 */
class PageController extends Controller
{
    /**
     * List pages
     *
     * Returns a paginated list of pages for the given website.
     *
     * @urlParam website string required The website ULID. Example: 01jv3kabc123def456ghi789jk
     * @queryParam page int Page number. Example: 1
     *
     * @response {
     *   "data": [{
     *     "id": "01jv3kxyz123def456ghi789ab",
     *     "website_id": "01jv3kabc123def456ghi789jk",
     *     "url": "https://example.com/about",
     *     "path": "/about",
     *     "slug": "about",
     *     "lastmod": "2026-04-01T00:00:00+00:00",
     *     "created_at": "2026-01-01T00:00:00+00:00",
     *     "updated_at": "2026-04-01T00:00:00+00:00"
     *   }],
     *   "links": {"first": "...", "last": "...", "prev": null, "next": null},
     *   "meta": {"current_page": 1, "per_page": 50, "total": 120}
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Website]."}
     */
    public function index(Request $request, string $website): AnonymousResourceCollection
    {
        $website = Website::where('user_id', $request->user()->id)->findOrFail($website);

        $pages = Page::where('website_id', $website->id)
            ->latest()
            ->paginate(50);

        return PageResource::collection($pages);
    }

    /**
     * Get a page
     *
     * Returns a single page including its latest extracted content.
     *
     * @urlParam website string required The website ULID. Example: 01jv3kabc123def456ghi789jk
     * @urlParam page string required The page ULID. Example: 01jv3kxyz123def456ghi789ab
     *
     * @response {
     *   "data": {
     *     "id": "01jv3kxyz123def456ghi789ab",
     *     "website_id": "01jv3kabc123def456ghi789jk",
     *     "url": "https://example.com/about",
     *     "path": "/about",
     *     "slug": "about",
     *     "lastmod": "2026-04-01T00:00:00+00:00",
     *     "has_content": true,
     *     "content": {
     *       "id": "01jv3kcon123def456ghi789cd",
     *       "page_id": "01jv3kxyz123def456ghi789ab",
     *       "content": {"head": {}, "body": {}},
     *       "extracted_at": "2026-05-01T09:30:00+00:00"
     *     },
     *     "created_at": "2026-01-01T00:00:00+00:00",
     *     "updated_at": "2026-04-01T00:00:00+00:00"
     *   }
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Page]."}
     */
    public function show(Request $request, string $website, string $page): PageResource
    {
        $website = Website::where('user_id', $request->user()->id)->findOrFail($website);

        $page = Page::with('latestContent')
            ->where('website_id', $website->id)
            ->findOrFail($page);

        return new PageResource($page);
    }
}
