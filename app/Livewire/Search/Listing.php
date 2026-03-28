<?php

namespace App\Livewire\Search;

use App\Domain\ContentExtraction\Models\PageContent;
use Livewire\Component;

class Listing extends Component
{
    public string $query = '';

    public $results = [];

    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->results = PageContent::search($this->query)
            ->take(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.search.listing');
    }
}
