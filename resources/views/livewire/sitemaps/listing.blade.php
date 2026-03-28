<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('websites.listing') }}" wire:navigate class="hover:underline">Websites
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('websites.detail', $website) }}" wire:navigate class="hover:underline">
            {{ $website->name }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Sitemaps</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <x-page-header :name="$website->name" :url="$website->url" />

    <div class="flex flex-col rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">


        <div class="overflow-auto">
            <div class="p-4">
                {{-- Table Header --}}
                <table class="w-full text-sm table-fixed">
                    <thead class="text-left text-gray-500 dark:text-zinc-400">
                        <tr class="border-b border-gray-200 dark:border-zinc-800">
                            <th class="pb-2 pr-4">URL</th>
                            <th class="pb-2 pr-4">Created</th>
                        </tr>
                    </thead>
                </table>

                {{-- Scrollable tbody --}}
                <div class="overflow-y-auto" style="max-height: 400px;">
                    <table class="w-full text-sm table-fixed">
                        <tbody>
                            @forelse ($sitemaps as $sitemap)
                                <tr
                                    class="border-b  border-gray-200 hover:bg-gray-50 dark:border-zinc-800/50 dark:hover:bg-zinc-800/30 text-gray-700 dark:text-zinc-300">
                                    <td class="py-3 pr-4">
                                        <a href="{{ $sitemap->url }}" target="_blank" rel="noopener noreferrer"
                                            class="hover:underline truncate block">
                                            {{ $sitemap->url }}
                                        </a>
                                    </td>
                                    <td class="py-3 pr-4 text-gray-400 dark:text-zinc-400">
                                        {{ $sitemap->created_at->format('Y-m-d H:i') }}</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500 dark:text-zinc-500">
                                        No sitemaps found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}

                <div class="mt-4">
                    {{ $sitemaps->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
