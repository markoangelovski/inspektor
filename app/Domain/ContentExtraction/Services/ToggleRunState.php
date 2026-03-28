<?php

namespace App\Domain\ContentExtraction\Services;

use App\Domain\ContentExtraction\Models\ContentExtractionRun;
use App\Domain\ContentExtraction\Jobs\StartContentExtractionRun;
use Illuminate\Support\Facades\Redis;

class ToggleRunState
{
    public function pause(ContentExtractionRun $run): void
    {
        $run->update(['status' => 'paused']);

        // Optional: Clear the specific queue to stop pending jobs immediately
        // Redis::del('queues:page-extraction'); 
    }

    public function resume(ContentExtractionRun $run): void
    {
        $run->update(['status' => 'running']);
        StartContentExtractionRun::dispatch($run->id)->onQueue('content-extraction');
    }
}
