<?php

namespace App\Http\Controllers;

use App\Models\PageAiCredit;
use App\Models\Website;
use Illuminate\Support\Str;

class AiCreditsExportController extends Controller
{
    public function __invoke(Website $website)
    {
        $totalCount = PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))->count();

        $result = PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))
            ->selectRaw('COUNT(*) as page_count, SUM(word_count) as total_words, SUM(credits_one_language) as total_credits_one, SUM(credits_five_languages) as total_credits_five')
            ->first();

        $totals = [
            'page_count' => (int) ($result->page_count ?? 0),
            'total_words' => (int) ($result->total_words ?? 0),
            'total_credits_one' => round((float) ($result->total_credits_one ?? 0), 4),
            'total_credits_five' => round((float) ($result->total_credits_five ?? 0), 4),
        ];

        $seen = [];
        $adjWords = 0;
        $adjCreditsOne = 0.0;
        $adjCreditsFive = 0.0;

        PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))
            ->get(['translatable_content'])
            ->each(function ($record) use (&$seen, &$adjWords, &$adjCreditsOne, &$adjCreditsFive) {
                foreach ($record->translatable_content ?? [] as $segment) {
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

        $adjustedTotals = [
            'total_words' => $adjWords,
            'total_credits_one' => round($adjCreditsOne, 4),
            'total_credits_five' => round($adjCreditsFive, 4),
        ];

        $strapiMdHtml = preg_replace(
            '/<a\s/i',
            '<a target="_blank" rel="noopener noreferrer" ',
            Str::markdown(file_get_contents(base_path('strapi.md')))
        );

        $filename = Str::slug($website->name).'_strapi_ai_credits.html';

        return response()->streamDownload(function () use ($website, $totals, $adjustedTotals, $strapiMdHtml, $totalCount) {
            echo $this->minify(view('exports.ai-credits-head', compact('website', 'totals', 'adjustedTotals', 'strapiMdHtml', 'totalCount'))->render());

            $i = 0;
            PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))
                ->orderBy('url')
                ->chunk(50, function ($rows) use (&$i) {
                    foreach ($rows as $row) {
                        $pageNum = intdiv($i, 25) + 1;
                        echo $this->minify(view('exports.ai-credits-row', compact('row', 'i', 'pageNum'))->render());
                        $i++;
                    }
                });

            echo $this->minify(view('exports.ai-credits-foot', compact('totalCount'))->render());
        }, $filename, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function minify(string $html): string
    {
        // Remove HTML comments (preserve IE conditional comments)
        $html = preg_replace('/<!--(?!\[if ).*?-->/s', '', $html);
        // Collapse whitespace between tags
        $html = preg_replace('/>\s+</s', '><', $html);
        // Strip leading whitespace (indentation) from every line
        $html = preg_replace('/^[ \t]+/m', '', $html);
        // Remove blank lines
        $html = preg_replace('/\n{2,}/', "\n", $html);

        return $html;
    }
}
