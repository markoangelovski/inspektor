<div>
    <x-inspektor.website-detail.breadcrumbs :website="$website" current="Content extraction" />

    <x-inspektor.website-detail.page-header :name="$website->name" :url="$website->url">
        <flux:dropdown>
            <flux:button icon:trailing="ellipsis-vertical" class="cursor-pointer"></flux:button>

            <flux:menu>
                <flux:menu.item icon="arrow-down-tray" class="cursor-pointer" wire:click="downloadCsv">Download as CSV
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </x-inspektor.website-detail.page-header>

    <x-inspektor.website-detail.page-nav-tabs :website="$website" />

    <livewire:content-extraction.status-card :website="$website" />
    <livewire:content-extraction.run-overview :website="$website" />
</div>
