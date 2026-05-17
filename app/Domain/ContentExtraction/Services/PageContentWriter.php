<?php

namespace App\Domain\ContentExtraction\Services;

use App\Domain\ContentExtraction\Models\PageContent;
use App\Models\Page;

class PageContentWriter
{
    public function write(Page $page, array $content): void
    {
        PageContent::updateOrCreate(
            ['page_id' => $page->id],
            [
                'content' => $content,
                'extracted_at' => now(),
            ]
        );
    }
}
