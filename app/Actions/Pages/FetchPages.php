<?php

namespace App\Actions\Pages;

use App\Models\Website;
use App\Jobs\ProcessPages;

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
