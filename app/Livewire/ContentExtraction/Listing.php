<?php

namespace App\Livewire\ContentExtraction;

use App\Models\Website;
use Livewire\Component;

class Listing extends Component
{
    public Website $website;

    public function mount(Website $website): void
    {
        $this->website = $website;
    }

    public function render()
    {
        return view('livewire.content-extraction.listing');
    }
}
