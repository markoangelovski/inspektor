<?php

namespace App\Livewire\Websites;

use Flux\Flux;
use App\Models\Website;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Actions\Pages\FetchPages;
use Livewire\Attributes\Validate;
use App\Actions\Sitemaps\AddSitemap;
use App\Actions\Sitemaps\FetchSitemaps;
use App\Actions\Websites\DeleteWebsite;

class Detail extends Component
{
    public Website $website;

    public bool $fetchingSitemaps = false;

    #[Validate([
        'required',
        'url',
        'max:2048',
        'regex:/\.xml$/i',
    ])]
    public string $sitemapUrl = '';

    public bool $fetchingPages = false;

    public function mount(Website $website)
    {
        $this->website = $website;
        $this->fetchingSitemaps = $website->sitemaps_processing;
    }

    #[On('website-edited')]
    public function refreshWebsite()
    {
        // Refresh the website in view
        $this->website->refresh();
    }

    public function deleteWebsite(DeleteWebsite $deleteWebsite)
    {
        $deleteWebsite->execute($this->website);

        // Redirect back to listing (SPA navigation)
        return $this->redirect(
            route('websites.listing'),
            navigate: true
        );
    }

    public function fetchSitemaps(FetchSitemaps $fetchSitemaps): void
    {
        // 1. Authorization (important)
        // $this->authorize('update', $this->website);

        // Guard against double clicks
        if ($this->fetchingSitemaps) return;

        // 2. Execute domain action
        $fetchSitemaps->execute($this->website);

        // 3. Optional UX feedback
        $this->fetchingSitemaps = true;

        // Optional: flash message
        session()->flash('status', 'Sitemap fetch has been queued.');
    }

    public function addSitemap(AddSitemap $addSitemap): void
    {
        // Optional (recommended later)
        // $this->authorize('update', $this->website);

        $this->validate();

        $addSitemap->execute(
            website: $this->website,
            url: $this->sitemapUrl,
        );

        // Reset input
        $this->reset('sitemapUrl');

        // Refresh relationship / counters
        $this->website->refresh();

        // Close modal
        Flux::modal('add-sitemap')->close();

        // Optional UX feedback
        session()->flash('status', 'Sitemap added.');
    }

    public function fetchPages(FetchPages $fetchPages): void
    {
        // Prevent double submission
        if ($this->fetchingPages || $this->website->pages_processing) {
            return;
        }

        // Authorization (enable later)
        // $this->authorize('update', $this->website);

        // Update local UI state immediately
        $this->fetchingPages = true;

        // Trigger the pipeline
        $fetchPages->execute($this->website);

        // UX feedback (optional)
        session()->flash('status', 'Page fetching has been queued.');
    }

    public function render()
    {
        return view('livewire.websites.detail');
    }
}
