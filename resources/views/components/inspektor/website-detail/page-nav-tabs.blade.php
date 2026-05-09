@props(['website'])

@php
    $activeTab = match (true) {
        request()->routeIs('pages.listing', 'pages.expand') => 'pages',
        request()->routeIs('content-extraction.listing') => 'content-extraction',
        default => 'info',
    };

    $active = 'border-[var(--color-accent-content)] text-[var(--color-accent-content)]';
    $inactive = 'border-transparent text-zinc-400 dark:text-white/50 hover:text-zinc-800 dark:hover:text-white';
@endphp

<div class="flex gap-4 h-10 border-b border-zinc-800/10 dark:border-white/20" role="tablist">

    <a href="{{ route('websites.detail', $website) }}" wire:navigate
        class="flex whitespace-nowrap gap-2 items-center px-2 -mb-px border-b-[2px] text-sm font-medium transition-colors cursor-pointer {{ $activeTab === 'info' ? $active : $inactive }}"
        role="tab" aria-selected="{{ $activeTab === 'info' ? 'true' : 'false' }}">
        <flux:icon.information-circle class="shrink-0 size-5" />
        Info
    </a>

    <a href="{{ route('pages.listing', $website) }}" wire:navigate
        class="flex whitespace-nowrap gap-2 items-center px-2 -mb-px border-b-[2px] text-sm font-medium transition-colors cursor-pointer {{ $activeTab === 'pages' ? $active : $inactive }}"
        role="tab" aria-selected="{{ $activeTab === 'pages' ? 'true' : 'false' }}">
        <flux:icon.document-text class="shrink-0 size-5" />
        Pages
    </a>

    <a href="{{ route('content-extraction.listing', $website) }}" wire:navigate
        class="flex whitespace-nowrap gap-2 items-center px-2 -mb-px border-b-[2px] text-sm font-medium transition-colors cursor-pointer {{ $activeTab === 'content-extraction' ? $active : $inactive }}"
        role="tab" aria-selected="{{ $activeTab === 'content-extraction' ? 'true' : 'false' }}">
        <flux:icon.document-magnifying-glass class="shrink-0 size-5" />
        Content extraction
    </a>

</div>
