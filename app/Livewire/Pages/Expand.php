<?php

namespace App\Livewire\Pages;

class Expand extends Listing
{
    public function render()
    {
        $pages = $this->getPages();

        // We use a specific 'fullscreen' layout for this view
        return view('livewire.pages.expand', [
            'pages' => $pages,
        ])->layout('components.layouts.fullscreen');
    }
}
