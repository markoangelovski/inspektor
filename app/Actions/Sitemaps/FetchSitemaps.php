<?php

namespace App\Actions\Sitemaps;

use App\Jobs\ProcessSitemaps;
use App\Models\Website;

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
