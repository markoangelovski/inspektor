<?php

namespace App\Livewire\AiCredits;

use App\Actions\AiCredits\CalculateWebsiteAiCreditsAction;
use App\Models\PageAiCredit;
use App\Models\Website;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    public Website $website;

    public bool $calculating = false;

    public string $strapiMdHtml = '';

    public function mount(Website $website): void
    {
        $this->website = $website;
        $this->strapiMdHtml = Str::markdown(file_get_contents(base_path('strapi.md')));

        if (! PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $website->id))->exists()) {
            $this->calculate();
        }
    }

    public function calculate(): void
    {
        $this->calculating = true;
        app(CalculateWebsiteAiCreditsAction::class)->execute($this->website);
        $this->calculating = false;
        $this->resetPage();
    }

    public function getTotalsProperty(): array
    {
        $result = PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $this->website->id))
            ->selectRaw('
                COUNT(*) as page_count,
                SUM(word_count) as total_words,
                SUM(credits_one_language) as total_credits_one,
                SUM(credits_five_languages) as total_credits_five
            ')
            ->first();

        return [
            'page_count' => (int) ($result->page_count ?? 0),
            'total_words' => (int) ($result->total_words ?? 0),
            'total_credits_one' => round((float) ($result->total_credits_one ?? 0), 4),
            'total_credits_five' => round((float) ($result->total_credits_five ?? 0), 4),
        ];
    }

    public function getCreditsProperty()
    {
        return PageAiCredit::whereHas('page', fn ($q) => $q->where('website_id', $this->website->id))
            ->orderBy('url')
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.ai-credits.listing', [
            'credits' => $this->credits,
            'totals' => $this->totals,
        ]);
    }
}
