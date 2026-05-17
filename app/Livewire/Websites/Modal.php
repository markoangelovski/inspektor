<?php

namespace App\Livewire\Websites;

use App\Actions\Websites\CreateWebsite;
use App\Actions\Websites\EditWebsite;
use App\Models\Website;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Modal extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:2048')]
    public string $url = '';

    #[Validate('nullable|string')]
    public ?string $meta_title = null;

    #[Validate('nullable|string')]
    public ?string $meta_description = null;

    #[Validate('nullable|string')]
    public ?string $meta_image_url = null;

    public ?string $websiteId = null;

    public bool $isEditMode = false;

    public function mount(string $initMode = 'create', ?string $websiteId = null): void
    {
        if ($initMode === 'edit' && $websiteId) {
            $website = Website::findOrFail($websiteId);
            $this->isEditMode = true;
            $this->websiteId = $website->id;
            $this->name = $website->name;
            $this->url = $website->url;
            $this->meta_title = $website->meta_title;
            $this->meta_description = $website->meta_description;
            $this->meta_image_url = $website->meta_image_url;
        }
    }

    public function saveWebsite(CreateWebsite $createWebsite, EditWebsite $editWebsite): void
    {
        $this->validate();

        if ($this->websiteId) {
            $editWebsite->execute($this->websiteId, $this->only(['name', 'url', 'meta_title', 'meta_description', 'meta_image_url']));
            $this->dispatch('website-edited');
        } else {
            $createWebsite->execute($this->only(['name', 'url']));
            $this->dispatch('website-created');
        }

        Flux::modal('website-modal')->close();
    }

    public function render()
    {
        return view('livewire.websites.modal');
    }
}
