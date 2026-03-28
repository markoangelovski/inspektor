<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\WebsiteMetadataFetcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessWebsiteMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $websiteId;

    public function __construct(string $websiteId)
    {
        $this->websiteId = $websiteId;
    }

    public function handle(WebsiteMetadataFetcher $fetcher): void
    {
        $website = Website::find($this->websiteId);

        // Website might have been deleted
        if (! $website) {
            return;
        }

        try {
            $metadata = $fetcher->fetch($website->url);

            $website->update([
                'meta_title'          => $metadata['title'] ?? null,
                'meta_description'    => $metadata['description'] ?? null,
                'meta_image_url'          => $metadata['image'] ?? null,
                'metadata_processed'  => true,
            ]);
        } catch (Throwable $e) {
            report($e);

            // IMPORTANT: still mark as processed to unblock UI
            $website->update([
                'metadata_processed' => true,
            ]);

            throw $e;
        }
    }
}
