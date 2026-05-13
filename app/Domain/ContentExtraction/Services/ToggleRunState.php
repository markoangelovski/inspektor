<?php

namespace App\Domain\ContentExtraction\Services;

use App\Domain\ContentExtraction\Jobs\ResumeContentExtractionRun;
use App\Domain\ContentExtraction\Models\ContentExtractionRun;

class ToggleRunState
{
    public function pause(ContentExtractionRun $run): void
    {
        $run->update(['status' => 'paused']);
    }

    public function resume(ContentExtractionRun $run): void
    {
        $run->update(['status' => 'running']);
        ResumeContentExtractionRun::dispatch($run->id)->onQueue('content-extraction');
    }
}
