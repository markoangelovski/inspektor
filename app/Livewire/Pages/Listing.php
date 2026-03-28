<?php

namespace App\Livewire\Pages;

use App\Models\Page;
use App\Models\Website;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    public Website $website;

    public string $search = '';
    public ?string $selectedPageId = null;

    /** REQUIRED for pagination hydration */
    public int $page = 1;

    /** @var int|string */
    public $perPage = 10;

    public array $perPageOptions = [10, 50, 100, 1000, 'all'];

    public ?Page $viewerPage = null;
    public bool $viewerOpen = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page'    => ['except' => 1],
    ];

    protected function getPages()
    {
        return $this->website
            ->pages()
            ->when(
                $this->search !== '',
                fn($query) =>
                $query->where('path', 'like', '%' . $this->search . '%')
            )
            ->orderBy('path')
            ->paginate($this->resolvePerPage());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Search term entered
    public function updatedSearch()
    {
        $this->resetPage();
        $this->broadcastUpdate('pages-updated');
    }

    public function updatingPerPage($value): void
    {
        if (!in_array($value, $this->perPageOptions, true)) {
            $this->perPage = 10;
            return;
        }

        $this->resetPage();
    }

    // Entries per page change
    public function updatedPerPage()
    {
        $this->resetPage();
        $this->broadcastUpdate();
    }

    // Pagination page change
    public function updatedPage()
    {
        $this->broadcastUpdate();
    }

    public function selectPage(string $pageId): void
    {
        $this->selectedPageId = $pageId;

        // Dispatch specifically for selection so React can center the node
        $this->broadcastUpdate();
    }

    protected function broadcastUpdate()
    {
        $pages = $this->getPages();

        $this->dispatch('pages-updated', [
            'pages' => $pages->values(),
            'selectedPageId' => $this->selectedPageId,
        ]);
    }

    protected function resolvePerPage(): int
    {
        if ($this->perPage === 'all') {
            // Hard safety cap to avoid accidental 100k row render
            return min(
                5000,
                $this->website->pages()->count()
            );
        }

        return (int) $this->perPage;
    }

    public function openViewer(string $pageId): void
    {
        $this->viewerPage = Page::findOrFail($pageId);
        $this->viewerOpen = true;
    }

    public function closeViewer(): void
    {
        $this->viewerOpen = false;
    }

    public function mount(Website $website): void
    {
        $this->website = $website;
    }

    public function render()
    {
        $pages = $this->website
            ->pages()
            ->when(
                $this->search !== '',
                fn($query) => $query->where('path', 'like', '%' . $this->search . '%')
            )
            ->orderBy('path')
            ->paginate($this->resolvePerPage());

        return view('livewire.pages.listing', [
            'pages'      => $pages,
            'totalCount' => $pages->total(),
        ]);
    }
}
