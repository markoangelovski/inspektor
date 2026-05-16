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
        $pageCount = 0;
        $rawWords = 0;
        $rawCreditsOne = 0.0;
        $rawCreditsFive = 0.0;
        $seen = [];
        $adjWords = 0;
        $adjCreditsOne = 0.0;
        $adjCreditsFive = 0.0;

        $website->pages()
            ->with('latestContent')
            ->get()
            ->each(function ($page) use ($now, &$pageCount, &$rawWords, &$rawCreditsOne, &$rawCreditsFive, &$seen, &$adjWords, &$adjCreditsOne, &$adjCreditsFive) {
                $content = $page->latestContent?->content ?? [];
                $segments = $this->calculator->extractTranslatableSegments($content);
                $wordCount = $this->calculator->wordCount($segments);
                $creditsOne = round(array_sum(array_column($segments, 'credits_one')), 4);
                $creditsFive = round(array_sum(array_column($segments, 'credits_five')), 4);

                PageAiCredit::updateOrCreate(
                    ['page_id' => $page->id],
                    [
                        'url' => $page->url,
                        'translatable_content' => $segments,
                        'word_count' => $wordCount,
                        'credits_one_language' => $creditsOne,
                        'credits_five_languages' => $creditsFive,
                        'calculated_at' => $now,
                    ]
                );

                $pageCount++;
                $rawWords += $wordCount;
                $rawCreditsOne += $creditsOne;
                $rawCreditsFive += $creditsFive;

                foreach ($segments as $segment) {
                    $text = $segment['text'] ?? '';
                    if ($text === '' || isset($seen[$text])) {
                        continue;
                    }
                    $seen[$text] = true;
                    $adjWords += (int) ($segment['word_count'] ?? 0);
                    $adjCreditsOne += (float) ($segment['credits_one'] ?? 0);
                    $adjCreditsFive += (float) ($segment['credits_five'] ?? 0);
                }
            });

        $website->update([
            'ai_credits_calculating' => false,
            'ai_credits_page_count' => $pageCount,
            'ai_credits_word_count' => $rawWords,
            'ai_credits_one_language' => round($rawCreditsOne, 4),
            'ai_credits_five_languages' => round($rawCreditsFive, 4),
            'ai_credits_unique_word_count' => $adjWords,
            'ai_credits_unique_one_language' => round($adjCreditsOne, 4),
            'ai_credits_unique_five_languages' => round($adjCreditsFive, 4),
            'ai_credits_calculated_at' => $now,
        ]);
    }
}
