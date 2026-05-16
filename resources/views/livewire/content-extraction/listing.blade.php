<div>
    <x-inspektor.website-detail.breadcrumbs :website="$website" current="Content extraction" />

    <x-inspektor.website-detail.page-header :name="$website->name" :url="$website->url" />

    <x-inspektor.website-detail.page-nav-tabs :website="$website" active="content-extraction" />

    <div class="space-y-6 mt-6">
        <livewire:content-extraction.status-card :website="$website" />
        <livewire:content-extraction.run-overview :website="$website" />
    </div>
</div>
