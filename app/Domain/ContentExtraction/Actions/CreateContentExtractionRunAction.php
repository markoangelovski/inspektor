<?php

namespace App\Domain\ContentExtraction\Actions;

use App\Domain\ContentExtraction\Jobs\StartContentExtractionRun;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Models\Website;

class CreateContentExtractionRunAction
{
    public function execute(Website $website, ?array $diff = null): ContentExtractionRun
    {
        ContentExtractionRun::where('website_id', $website->id)
            ->whereNotIn('status', ['completed', 'completed_with_errors', 'failed'])
            ->update(['status' => 'failed', 'finished_at' => now()]);

        $run = ContentExtractionRun::create([
            'website_id' => $website->id,
            'created_by' => auth()->id(),
            'status' => 'pending',
            'diff' => $diff,
            'total_pages' => $website->pages()->count(),
            'processed_pages' => 0,
        ]);

        StartContentExtractionRun::dispatch($run->id)
            ->onQueue('content-extraction');

        return $run;
    }
}
