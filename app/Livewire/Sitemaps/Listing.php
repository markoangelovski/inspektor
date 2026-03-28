<?php

namespace App\Livewire\Sitemaps;

use App\Models\Website;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    public Website $website;

    public function mount(Website $website)
    {
        $this->website = $website;
    }

    public function render()
    {
        return view('livewire.sitemaps.listing', [
            'sitemaps' => $this->website
                ->sitemaps()
                ->latest()
                ->paginate(10),
        ]);
    }
}
