<?php

namespace App\Actions\Sitemaps;

use App\Models\Website;
use App\Jobs\ProcessSitemaps;

class FetchSitemaps
{
    public function execute(Website $website): void
    {

        $updated = Website::query()
            ->whereKey($website->id)
            ->where('sitemaps_processing', false)
            ->update(['sitemaps_processing' => true]);

        if ($updated === 0) {
            // Someone else already started processing
            return;
        }

        // Dispatch async job
        ProcessSitemaps::dispatch($website->id);
    }
}
