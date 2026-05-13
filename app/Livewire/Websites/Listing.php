<?php

namespace App\Livewire\Websites;

use App\Models\Website;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    #[On('website-created')]
    public function refreshWebsites()
    {
        // Reset pagination so new item appears
        $this->resetPage();
    }

    public function render()
    {
        $websites = Website::query()->orderBy('id', 'DESC')->paginate(50);

        return view('livewire.websites.listing', compact('websites'));
    }
}
