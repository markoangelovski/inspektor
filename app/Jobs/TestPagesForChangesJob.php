<?php

namespace App\Jobs;

use App\Actions\Websites\TestPagesForChangesAction;
use App\Models\Website;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class TestPagesForChangesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $websiteId) {}

    public function handle(TestPagesForChangesAction $action): void
    {
        $website = Website::find($this->websiteId);

        if (! $website) {
            $this->clearTransientKeys();

            return;
        }

        $sitemapData = Cache::get(TestSitemapsForChangesJob::sitemapDataKey($this->websiteId));

        if (! $sitemapData) {
            Cache::put(
                TestSitemapsForChangesJob::resultKey($this->websiteId),
                [
                    'hasChanges' => false,
                    'message' => '',
                    'details' => [],
                    'error' => 'Sitemap data expired before page test could run. Please try again.',
                ],
                now()->addHour()
            );
            $this->clearTransientKeys();

            return;
        }

        $pageResult = $action->execute($website, $sitemapData['freshSitemapUrls']);

        $allDetails = array_merge($sitemapData['details'], $pageResult['details']);
        $hasChanges = $sitemapData['hasChanges'] || $pageResult['hasChanges'];

        if (empty($allDetails)) {
            $allDetails = [['label' => 'All sitemaps and pages match the stored data.', 'items' => []]];
        }

        Cache::put(
            TestSitemapsForChangesJob::resultKey($this->websiteId),
            [
                'hasChanges' => $hasChanges,
                'message' => $hasChanges
                    ? 'Changes detected since the last scan.'
                    : 'No changes detected since the last scan.',
                'details' => $allDetails,
                'error' => null,
            ],
            now()->addHour()
        );

        $this->clearTransientKeys();
    }

    public function failed(\Throwable $e): void
    {
        Cache::put(
            TestSitemapsForChangesJob::resultKey($this->websiteId),
            [
                'hasChanges' => false,
                'message' => '',
                'details' => [],
                'error' => 'Page test failed: '.$e->getMessage(),
            ],
            now()->addHour()
        );
        $this->clearTransientKeys();
    }

    private function clearTransientKeys(): void
    {
        Cache::forget(TestSitemapsForChangesJob::pendingKey($this->websiteId));
        Cache::forget(TestSitemapsForChangesJob::sitemapDataKey($this->websiteId));
    }
}
