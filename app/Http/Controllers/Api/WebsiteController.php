<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebsiteResource;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Websites
 */
class WebsiteController extends Controller
{
    /**
     * List websites
     *
     * Returns a paginated list of websites belonging to the authenticated user.
     *
     * @queryParam page int Page number. Example: 1
     *
     * @response {
     *   "data": [{
     *     "id": "01jv3kabc123def456ghi789jk",
     *     "name": "My Website",
     *     "url": "https://example.com",
     *     "type": "standard",
     *     "meta_title": "Example Domain",
     *     "meta_description": "A sample website.",
     *     "meta_image_url": null,
     *     "pages_count": 120,
     *     "pages_last_sync": "2026-05-01T10:00:00+00:00",
     *     "created_at": "2026-01-01T00:00:00+00:00",
     *     "updated_at": "2026-05-01T10:00:00+00:00"
     *   }],
     *   "links": {"first": "...", "last": "...", "prev": null, "next": null},
     *   "meta": {"current_page": 1, "per_page": 25, "total": 1}
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $websites = Website::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(25);

        return WebsiteResource::collection($websites);
    }

    /**
     * Get a website
     *
     * @urlParam website string required The website ULID. Example: 01jv3kabc123def456ghi789jk
     *
     * @response {
     *   "data": {
     *     "id": "01jv3kabc123def456ghi789jk",
     *     "name": "My Website",
     *     "url": "https://example.com",
     *     "type": "standard",
     *     "meta_title": "Example Domain",
     *     "meta_description": "A sample website.",
     *     "meta_image_url": null,
     *     "pages_count": 120,
     *     "pages_last_sync": "2026-05-01T10:00:00+00:00",
     *     "created_at": "2026-01-01T00:00:00+00:00",
     *     "updated_at": "2026-05-01T10:00:00+00:00"
     *   }
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Website]."}
     */
    public function show(Request $request, string $website): WebsiteResource
    {
        $website = Website::where('user_id', $request->user()->id)
            ->findOrFail($website);

        return new WebsiteResource($website);
    }
}
