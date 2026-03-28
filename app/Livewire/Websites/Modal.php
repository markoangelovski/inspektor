<?php

namespace App\Livewire\Websites;

use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Actions\Websites\EditWebsite;
use App\Actions\Websites\CreateWebsite;

class Modal extends Component
{
    #[Validate("required|string|max:255")]
    public string $name;

    #[Validate("required|string|max:2048")]
    public string $url;

    #[Validate("nullable|string")]
    public $meta_title = null;

    #[Validate("nullable|string")]
    public $meta_description = null;

    #[Validate("nullable|string")]
    public $meta_image_url = null;

    public $websiteId = null;
    public bool $isEditMode = false;

    public function saveWebsite(CreateWebsite $createWebsite, EditWebsite $editWebsite)
    {
        $this->validate();

        if ($this->websiteId) {
            $editWebsite->execute($this->websiteId, $this->only(["name", "url", "type", "meta_title", "meta_description", "meta_image_url"]));
            $this->dispatch('website-edited');
        } else {
            $createWebsite->execute($this->only(["name", "url", "type"]));
            $this->dispatch('website-created');
        }

        $this->reset();

        Flux::modal("website-modal")->close();
    }

    #[On("open-website-modal")]
    public function openWebsiteModal($mode, $website = null)
    {

        $this->isEditMode = $mode === "edit";

        if ($mode === "create") {
            $this->isEditMode = false;
            $this->websiteId = null;

            $this->reset();
        } else {
            $this->websiteId = $website["id"];

            $this->name = $website["name"];
            $this->url = $website["url"];
            $this->meta_title = $website["meta_title"];
            $this->meta_description = $website["meta_description"];
            $this->meta_image_url = $website["meta_image_url"];
        }
    }

    public function render()
    {
        return view('livewire.websites.modal');
    }
}
