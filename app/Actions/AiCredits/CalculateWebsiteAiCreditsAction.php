<?php

namespace App\Actions\AiCredits;

use App\Models\PageAiCredit;
use App\Models\Website;
use App\Services\AiCreditCalculator;
use Illuminate\Support\Carbon;

class CalculateWebsiteAiCreditsAction
{
    public function __construct(private AiCreditCalculator $calculator) {}

    public function execute(Website $website): void
    {
        $now = Carbon::now();

        $website->pages()
            ->with('latestContent')
            ->get()
            ->each(function ($page) use ($now) {
                $content = $page->latestContent?->content ?? [];
                $segments = $this->calculator->extractTranslatableSegments($content);
                $wordCount = $this->calculator->wordCount($segments);

                PageAiCredit::updateOrCreate(
                    ['page_id' => $page->id],
                    [
                        'url' => $page->url,
                        'translatable_content' => $segments,
                        'word_count' => $wordCount,
                        'credits_one_language' => round(array_sum(array_column($segments, 'credits_one')), 4),
                        'credits_five_languages' => round(array_sum(array_column($segments, 'credits_five')), 4),
                        'calculated_at' => $now,
                    ]
                );
            });
    }
}
