<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Support\Str;

class AiCreditsExportController extends Controller
{
    public function __invoke(Website $website)
    {
        $totalCount = (int) ($website->ai_credits_page_count ?? 0);

        $totals = [
            'page_count' => $totalCount,
            'total_words' => (int) ($website->ai_credits_word_count ?? 0),
            'total_credits_one' => (float) ($website->ai_credits_one_language ?? 0),
            'total_credits_five' => (float) ($website->ai_credits_five_languages ?? 0),
        ];

        $adjustedTotals = [
            'total_words' => (int) ($website->ai_credits_unique_word_count ?? 0),
            'total_credits_one' => (float) ($website->ai_credits_unique_one_language ?? 0),
            'total_credits_five' => (float) ($website->ai_credits_unique_five_languages ?? 0),
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
