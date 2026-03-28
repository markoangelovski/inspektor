<?php

namespace App\Domain\ContentExtraction\Services;

use App\Models\Page;
use App\Domain\ContentExtraction\Models\PageContent;

class PageContentWriter
{
    public function write(Page $page, array $content): void
    {
        // Since we only care about one version, we update or create based on page_id
        PageContent::updateOrCreate(
            ['page_id' => $page->id],
            [
                'content' => $content,
                'extractor_version' => 'v1', // Hardcoded simple version
                'extracted_at' => now(),
            ]
        );
    }
}
