@props(['website', 'current' => null])

<flux:breadcrumbs class="mb-6">
    <flux:breadcrumbs.item href="{{ route('websites.listing') }}" wire:navigate class="hover:underline">Websites
    </flux:breadcrumbs.item>
    @if ($current)
        <flux:breadcrumbs.item href="{{ route('websites.detail', $website) }}" wire:navigate class="hover:underline">
            {{ $website->name }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $current }}</flux:breadcrumbs.item>
    @else
        <flux:breadcrumbs.item>{{ $website->name }}</flux:breadcrumbs.item>
    @endif
</flux:breadcrumbs>
