<?php

namespace App\Livewire\Websites;

use App\Actions\Pages\FetchPages;
use App\Actions\Sitemaps\AddSitemap;
use App\Actions\Sitemaps\FetchSitemaps;
use App\Actions\Websites\DeleteWebsite;
use App\Models\Website;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Detail extends Component
{
    use WithPagination;

    public Website $website;

    public bool $fetchingSitemaps = false;

    #[Validate('required|string|max:65535')]
    public string $sitemapInput = '';

    public bool $fetchingPages = false;

    public function mount(Website $website)
    {
        $this->website = $website;
        $this->fetchingSitemaps = $website->sitemaps_processing;
        $this->fetchingPages = $website->pages_processing
            || ($website->sitemaps_fetched && ! $website->pages_fetched);
    }

    public function refreshData(): void
    {
        $this->website->refresh();

        if (! $this->website->sitemaps_processing) {
            $this->fetchingSitemaps = false;
        }

        // Hold fetchingPages true through the gap between sitemaps completing
        // and the pages job starting (they are auto-queued in sequence).
        if ($this->website->sitemaps_fetched && ! $this->website->pages_fetched) {
            $this->fetchingPages = true;
        } elseif ($this->website->pages_fetched) {
            $this->fetchingPages = false;
        }
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
        if ($this->fetchingSitemaps) {
            return;
        }

        // 2. Execute domain action
        $fetchSitemaps->execute($this->website);

        // 3. Optional UX feedback
        $this->fetchingSitemaps = true;
    }

    public function addSitemap(AddSitemap $addSitemap): void
    {
        $this->validate();

        $entries = $this->parseSitemapInput($this->sitemapInput);

        if (empty($entries)) {
            $this->addError('sitemapInput', 'No valid sitemap URLs found.');

            return;
        }

        foreach ($entries as $entry) {
            $addSitemap->execute(
                website: $this->website,
                url: $entry['url'],
                lastmod: $entry['lastmod'],
            );
        }

        $this->reset('sitemapInput');
        $this->website->refresh();
        Flux::modal('add-sitemap')->close();
    }

    private function parseSitemapInput(string $input): array
    {
        $input = trim($input);

        if (
            str_contains($input, '<sitemap') ||
            str_contains($input, '<urlset') ||
            str_contains($input, '<sitemapindex')
        ) {
            return $this->parseXmlSitemaps($input);
        }

        $entries = [];
        foreach (explode("\n", $input) as $line) {
            $url = trim($line);
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                $entries[] = ['url' => $url, 'lastmod' => null];
            }
        }

        return $entries;
    }

    private function parseXmlSitemaps(string $xml): array
    {
        $entries = [];

        try {
            $prev = libxml_use_internal_errors(true);
            $doc = simplexml_load_string($xml);
            libxml_use_internal_errors($prev);

            if (! $doc) {
                return [];
            }

            $ns = $doc->getNamespaces(true);
            $defaultNs = $ns[''] ?? null;

            if ($defaultNs) {
                $doc->registerXPathNamespace('sm', $defaultNs);
                $locs = $doc->xpath('//sm:loc');
                $lastmods = $doc->xpath('//sm:lastmod');
            } else {
                $locs = $doc->xpath('//loc');
                $lastmods = $doc->xpath('//lastmod');
            }

            foreach ($locs as $i => $loc) {
                $url = trim((string) $loc);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $entries[] = [
                        'url' => $url,
                        'lastmod' => isset($lastmods[$i]) ? trim((string) $lastmods[$i]) : null,
                    ];
                }
            }
        } catch (\Exception) {
            // Return empty on parse failure
        }

        return $entries;
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
    }

    public function render()
    {
        return view('livewire.websites.detail', [
            'sitemaps' => $this->website->sitemaps()->latest()->paginate(10),
        ]);
    }
}
