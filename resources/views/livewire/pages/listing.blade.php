<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('websites.listing') }}" wire:navigate class="hover:underline">Websites
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('websites.detail', $website) }}" wire:navigate class="hover:underline">
            {{ $website->name }}
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Pages</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <x-page-header :name="$website->name" :url="$website->url" />

    <div class="flex flex-col rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        {{-- Header --}}
        <div class="p-4 border-b border-gray-200 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-4 gap-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-zinc-100">
                    Pages
                </h2>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500 dark:text-zinc-500">
                        {{ $totalCount }} pages
                    </span>

                    {{-- Per-page selector --}}
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 dark:text-zinc-400">
                            Show
                        </span>

                        <select wire:model.live="perPage"
                            class=" rounded-md border px-2 py-1 bg-white text-gray-900 border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-700">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}">
                                    {{ is_numeric($option) ? $option : 'All' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <div wire:loading wire:target="perPage" class="text-xs text-gray-400">
                    Updating…
                </div>
            </div>

            {{-- Search --}}
            <div class="relative">
                {{-- Heroicon: magnifying-glass --}}
                <svg xmlns="http://www.w3.org/2000/svg"
                    class=" absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 dark:text-zinc-500"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                </svg>

                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search pages by path"
                    class=" w-full bg-white text-gray-900 placeholder-gray-400 border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:border-zinc-700" />
            </div>

        </div>

        {{-- Table --}}
        <div class="overflow-auto">
            <div class="p-4">
                {{-- Table Header --}}
                <table class="w-full text-sm table-fixed">
                    <thead class="text-left text-gray-500 dark:text-zinc-400">
                        <tr class="border-b border-gray-200 dark:border-zinc-800">
                            <th class="pb-2 pr-4">Path</th>
                            <th class="pb-2 pr-4">Slug</th>
                            <th class="pb-2 pr-4">Date Created</th>
                            <th class="pb-2 text-center">View Content</th>
                        </tr>
                    </thead>
                </table>

                {{-- Scrollable tbody --}}
                <div class="overflow-y-auto" style="max-height: 400px;">
                    <table class="w-full text-sm table-fixed">
                        <tbody>
                            @forelse ($pages as $page)
                                <tr wire:key="page-{{ $page->id }}" wire:click="selectPage('{{ $page->id }}')"
                                    id="page-row-{{ $page->id }}"
                                    class="border-b cursor-pointer transition-colors border-gray-200 hover:bg-gray-50 dark:border-zinc-800/50 dark:hover:bg-zinc-800/30 {{ $selectedPageId === $page->id ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'text-gray-700 dark:text-zinc-300' }}">
                                    <td class="py-3 pr-4">{{ $page->path }}</td>
                                    <td class="py-3 pr-4 text-gray-400 dark:text-zinc-400">{{ $page->slug }}</td>
                                    <td class="py-3 pr-4 text-xs text-gray-500 dark:text-zinc-500">
                                        {{ $page->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="py-3 text-center">
                                        <button type="button" wire:click.stop="openViewer('{{ $page->id }}')"
                                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-zinc-400 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                                            title="View content">
                                            <flux:icon.eye class="size-4" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500 dark:text-zinc-500">
                                        No pages found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($perPage !== 'all')
                    <div class="mt-4">
                        {{ $pages->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Drawer --}}
        <x-inspector.page-viewer-drawer :viewer-page="$viewerPage" />

        {{-- Information Architecture --}}
        <div x-init="window.addEventListener('pages:view-content', (e) => {
            $wire.openViewer(e.detail.pageId)
        });
        window.addEventListener('pages:page-selected', (e) => {
            const pageId = e.detail.pageId;
        
            // 1. Update Livewire state
            $wire.selectPage(pageId);
        
            // 2. Scroll the table row into view
            // We use a tiny timeout to ensure the DOM is ready 
            // (especially if the table just re-rendered)
            setTimeout(() => {
                const row = document.getElementById(`page-row-${pageId}`);
                if (row) {
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }, 50);
        });" class="mt-8 px-4 space-y-4">
            <div class="px-4 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-zinc-100">
                    Information Architecture
                </h2>

                <div class="flex gap-2">
                    {{-- <button type="button"
                        class="px-3 py-1 text-sm rounded-md
                       bg-gray-100 hover:bg-gray-200
                       dark:bg-zinc-800 dark:hover:bg-zinc-700">
                        Expand all
                    </button> --}}

                    <flux:button variant="subtle" icon="arrows-pointing-out" title="Expand to Fullscreen"
                        {{-- 1. Use a dynamic wire:key to force Livewire to refresh the href --}}
                        wire:key="expand-button-page-{{ $pages->currentPage() }}-{{ $search }}"
                        href="{{ route('pages.expand', [
                            'website' => $website,
                            'search' => $search,
                            'perPage' => $perPage,
                            'page' => $pages->currentPage(),
                        ]) }}"
                        target="_blank">
                        Expand
                    </flux:button>
                </div>
            </div>



            <div wire:ignore id="pages-flow-root" class="h-[600px] w-full" data-pages='@json($pages->values())'>
            </div>
        </div>

    </div>

</div>
