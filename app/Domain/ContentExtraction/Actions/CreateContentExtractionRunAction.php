<?php

namespace App\Domain\ContentExtraction\Actions;

use App\Models\Website;
use App\Domain\ContentExtraction\Enums\ExtractionMode;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Jobs\StartContentExtractionRun;

class CreateContentExtractionRunAction
{
    public function execute(Website $website): ContentExtractionRun
    {
        // 1. Mark any existing active runs as Failed to clear the path
        ContentExtractionRun::where('website_id', $website->id)
            ->whereNotIn('status', ['completed', 'completed_with_errors', 'failed'])
            ->update(['status' => 'failed', 'finished_at' => now()]);

        // 2. Create the new run
        $run = ContentExtractionRun::create([
            'website_id' => $website->id,
            'status' => 'pending',
            'mode' => ExtractionMode::Initial,
            'extractor_version' => 'readability-v1',
            'total_pages' => $website->pages()->count(),
            'processed_pages' => 0,
        ]);

        StartContentExtractionRun::dispatch($run->id)
            ->onQueue('content-extraction');

        return $run;
    }
}
