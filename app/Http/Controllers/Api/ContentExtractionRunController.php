<?php

namespace App\Http\Controllers\Api;

use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentExtractionRunResource;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Content Extraction Runs
 */
class ContentExtractionRunController extends Controller
{
    /**
     * List extraction runs
     *
     * Returns a paginated list of content extraction runs for the given website, latest first.
     *
     * @urlParam website_id string required The website ULID. Example: 01jv3kabc123def456ghi789jk
     * @queryParam page int Page number. Example: 1
     *
     * @response {
     *   "data": [{
     *     "id": "01jv3krun123def456ghi789ef",
     *     "website_id": "01jv3kabc123def456ghi789jk",
     *     "status": "completed",
     *     "total_pages": 120,
     *     "processed_pages": 120,
     *     "progress": 100,
     *     "started_at": "2026-05-01T09:00:00+00:00",
     *     "finished_at": "2026-05-01T09:45:00+00:00",
     *     "created_at": "2026-05-01T08:59:00+00:00"
     *   }],
     *   "links": {"first": "...", "last": "...", "prev": null, "next": null},
     *   "meta": {"current_page": 1, "per_page": 25, "total": 3}
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Website]."}
     */
    public function index(Request $request, string $website): AnonymousResourceCollection
    {
        $website = Website::where('user_id', $request->user()->id)->findOrFail($website);

        $runs = ContentExtractionRun::where('website_id', $website->id)
            ->latest()
            ->paginate(25);

        return ContentExtractionRunResource::collection($runs);
    }

    /**
     * Get an extraction run
     *
     * @urlParam website_id string required The website ULID. Example: 01jv3kabc123def456ghi789jk
     * @urlParam id string required The extraction run ULID. Example: 01jv3krun123def456ghi789ef
     *
     * @response {
     *   "data": {
     *     "id": "01jv3krun123def456ghi789ef",
     *     "website_id": "01jv3kabc123def456ghi789jk",
     *     "status": "completed",
     *     "total_pages": 120,
     *     "processed_pages": 120,
     *     "progress": 100,
     *     "started_at": "2026-05-01T09:00:00+00:00",
     *     "finished_at": "2026-05-01T09:45:00+00:00",
     *     "created_at": "2026-05-01T08:59:00+00:00"
     *   }
     * }
     * @response 404 {"message": "No query results for model."}
     */
    public function show(Request $request, string $website, string $content_extraction_run): ContentExtractionRunResource
    {
        $website = Website::where('user_id', $request->user()->id)->findOrFail($website);

        $run = ContentExtractionRun::where('website_id', $website->id)->findOrFail($content_extraction_run);

        return new ContentExtractionRunResource($run);
    }
}
