<?php

namespace App\Actions\Pages;

use App\Jobs\ProcessPages;
use App\Models\Website;

class FetchPages
{
    public function execute(Website $website): void
    {
        // Guard: prevent duplicate processing
        if ($website->pages_processing) {
            return;
        }

        // Transition website state
        $website->update([
            'pages_processing' => true,
            'pages_message' => null,
        ]);

        // Dispatch async job
        ProcessPages::dispatch($website->id);
    }
}
