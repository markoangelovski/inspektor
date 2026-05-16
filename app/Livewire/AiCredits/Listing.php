<?php

namespace App\Livewire\AiCredits;

use App\Jobs\CalculateWebsiteAiCreditsJob;
use App\Models\PageAiCredit;
use App\Models\Website;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    public Website $website;

    public string $strapiMdHtml = '';

    public array $loadedSegments = [];

    public function mount(Website $website): void
    {
        $this->website = $website;
        $this->strapiMdHtml = preg_replace(
            '/<a\s/i',
            '<a target="_blank" rel="noopener noreferrer" ',
            Str::markdown(file_get_contents(base_path('strapi.md')))
        );

        if ($website->ai_credits_calculated_at === null && ! $website->ai_credits_calculating) {
            $website->update(['ai_credits_calculating' => true]);
            CalculateWebsiteAiCreditsJob::dispatch($website);
        }
    }

    public function calculate(): void
    {
        $this->loadedSegments = [];
        $this->website->update(['ai_credits_calculating' => true]);
        $this->website->refresh();
        CalculateWebsiteAiCreditsJob::dispatch($this->website);
        $this->resetPage();
    }

    public function checkCalculating(): void
    {
        $this->website->refresh();
    }

    public function loadSegments(string $creditId): void
    {
        $credit = PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $this->website->id))
            ->where('id', $creditId)
            ->select(['id', 'translatable_content'])
            ->first();

        if ($credit) {
            $this->loadedSegments[$creditId] = $credit->translatable_content ?? [];
        }
    }

    public function getTotalsProperty(): array
    {
        return [
            'page_count' => (int) ($this->website->ai_credits_page_count ?? 0),
            'total_words' => (int) ($this->website->ai_credits_word_count ?? 0),
            'total_credits_one' => (float) ($this->website->ai_credits_one_language ?? 0),
            'total_credits_five' => (float) ($this->website->ai_credits_five_languages ?? 0),
        ];
    }

    public function getAdjustedTotalsProperty(): array
    {
        return [
            'total_words' => (int) ($this->website->ai_credits_unique_word_count ?? 0),
            'total_credits_one' => (float) ($this->website->ai_credits_unique_one_language ?? 0),
            'total_credits_five' => (float) ($this->website->ai_credits_unique_five_languages ?? 0),
        ];
    }

    public function getCreditsProperty()
    {
        return PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $this->website->id))
            ->select(['id', 'page_id', 'url', 'word_count', 'credits_one_language', 'credits_five_languages'])
            ->orderBy('url')
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.ai-credits.listing', [
            'credits'       => $this->credits,        // → getCreditsProperty()
            'totals'        => $this->totals,         // → getTotalsProperty()
            'adjustedTotals' => $this->adjustedTotals, // → getAdjustedTotalsProperty()
        ]);
    }
}
