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

        $filename = Str::slug($website->name).'_strapi_ai_credits.html';

        return response()->streamDownload(function () use ($website, $totals, $totalCount) {
            echo view('exports.ai-credits-head', compact('website', 'totals', 'totalCount'))->render();

            $i = 0;
            PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))
                ->orderBy('url')
                ->chunk(50, function ($rows) use (&$i) {
                    foreach ($rows as $row) {
                        $pageNum = intdiv($i, 25) + 1;
                        echo view('exports.ai-credits-row', compact('row', 'i', 'pageNum'))->render();
                        $i++;
                    }
                });

            echo view('exports.ai-credits-foot', compact('totalCount'))->render();
        }, $filename, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
