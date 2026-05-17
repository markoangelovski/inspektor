<div class="space-y-6">
    @if (
        !$website->metadata_processed ||
            $fetchingSitemaps ||
            $website->sitemaps_processing ||
            $fetchingPages ||
            $website->pages_processing)
        <div wire:poll.3s="refreshData"></div>
    @endif

    <x-inspektor.website-detail.breadcrumbs :website="$website" current="Info" />

    <x-inspektor.website-detail.page-header :name="$website->name" :url="$website->url">
        <flux:dropdown>
            <flux:button icon:trailing="cog-6-tooth" class="cursor-pointer"></flux:button>

            <flux:menu>
                <flux:modal.trigger name="website-modal">
                    <flux:menu.item icon="pencil-square" class="cursor-pointer">Edit</flux:menu.item>
                </flux:modal.trigger>

                <flux:modal.trigger name="delete-website">
                    <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">Delete</flux:menu.item>
                </flux:modal.trigger>
            </flux:menu>
        </flux:dropdown>
    </x-inspektor.website-detail.page-header>

    {{-- Create/Edit website modal --}}
    <livewire:websites.modal init-mode="edit" :website-id="$website->id" />

    {{-- Delete website modal --}}
    <flux:modal name="delete-website" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete {{ $website->name }}?</flux:heading>

                <flux:text class="mt-2">
                    You're about to delete this website.<br>
                    This action cannot be reversed.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="deleteWebsite" type="submit" variant="danger" class="cursor-pointer">Delete
                    website</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Nav tabs --}}
    <x-inspektor.website-detail.page-nav-tabs :website="$website" active="info" />

    <div class="space-y-6 mt-6">
        {{-- Website metadata --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Image --}}
            <div class="">
                <span class="block text-sm mb-2">Image</span>

                <div class="w-full aspect-video rounded-lg border border-gray-200 overflow-hidden">
                    @if (!$website->metadata_processed)
                        <div
                            class="w-full h-full bg-gray-200 rounded animate-pulse flex items-center justify-center text-gray-400 text-sm">
                            Metadata processing in progress...
                        </div>
                    @elseif ($website->meta_image_url)
                        <img src="{{ $website->meta_image_url }}" class="w-full h-full object-cover" alt="Meta image">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                            No image
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">

                {{-- Title --}}
                <div>
                    <span class="block text-sm mb-2">Title</span>

                    @if (!$website->metadata_processed)
                        <div
                            class="h-12 bg-gray-200 rounded-lg animate-pulse flex items-center justify-center text-gray-400 text-sm">
                            Metadata processing in progress...</div>
                    @else
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-500 border rounded-lg">
                            {{ $website->meta_title ?? $website->name }}
                        </div>
                    @endif
                </div>

                {{-- Description --}}
                <div>
                    <span class="block text-sm mb-2">Description</span>

                    @if (!$website->metadata_processed)
                        <div class="space-y-2">
                            <div
                                class="h-28 bg-gray-200 rounded-lg animate-pulse flex items-center justify-center text-gray-400 text-sm">
                                Metadata processing in progress..</div>
                        </div>
                    @else
                        <div
                            class="px-4 py-3 bg-gray-50 dark:bg-gray-500 border rounded-lg h-28 overflow-hidden overflow-y-scroll">
                            {{ $website->meta_description ?? '—' }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- ================== SITEMAPS CARD ================== --}}
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-6">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.map class="size-[18px] text-gray-400 dark:text-zinc-400" />
                        <h3 class="text-lg font-medium">Sitemaps</h3>
                    </div>
                    <div class="flex gap-2">
                        <flux:modal.trigger name="add-sitemap">
                            <flux:button icon="plus" size="sm" class="cursor-pointer">
                                Add Sitemap
                            </flux:button>
                        </flux:modal.trigger>
                        <flux:button wire:click="fetchSitemaps" wire:loading.attr="disabled"
                            :disabled="$fetchingSitemaps" size="sm" class="cursor-pointer">
                            <span class="flex items-center gap-1">
                                <flux:icon.arrow-path class="size-4 {{ $fetchingSitemaps ? 'animate-spin' : '' }}" />
                                {{ $website->sitemaps_fetched ? 'Refresh' : 'Fetch' }}
                            </span>
                        </flux:button>
                    </div>
                </div>

                {{-- Content --}}
                @if ($fetchingSitemaps)
                    <div class="flex items-center gap-3">
                        <flux:icon.arrow-path class="size-8 text-blue-400 animate-spin" />
                        <span class="text-sm text-gray-400 dark:text-zinc-400">Fetching sitemaps...</span>
                    </div>
                @else
                    <div class="text-5xl mb-2">{{ $website['sitemaps_count'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-zinc-500">Total sitemaps</div>
                    @if (!empty($website['sitemaps_last_sync']))
                        <div class="text-xs text-gray-400 dark:text-zinc-600 mt-1"
                            title="{{ $website->sitemaps_last_sync }}">
                            Last synced: {{ $website->sitemaps_last_sync->diffForHumans() }}
                        </div>
                    @endif
                    @if ($website['sitemaps_message'] !== 'ok')
                        <p class="text-xs text-gray-500 dark:text-zinc-500 mt-3">
                            {{ $website->sitemaps_message }}
                        </p>
                    @endif
                @endif

                <flux:modal name="add-sitemap" class="md:w-[32rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Add sitemaps</flux:heading>
                        </div>
                        <div class="space-y-2">
                            <flux:textarea wire:model.defer="sitemapInput" label="Sitemaps" rows="8"
                                placeholder="https://example.com/sitemap.xml&#10;https://example.com/sitemap2.xml&#10;&#10;Or paste XML sitemap index content..." />
                            <flux:text class="text-xs text-gray-500 dark:text-zinc-400">
                                Enter one URL per line, or paste the full contents of an XML sitemap index — URLs and last-modified dates will be extracted automatically.
                            </flux:text>
                        </div>
                        <div class="flex">
                            <flux:spacer />
                            <flux:button wire:click="addSitemap" wire:loading.attr="disabled" type="submit"
                                variant="primary" class="cursor-pointer">Save changes
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>

            </div>

            {{-- ================== PAGES CARD ================== --}}
            <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-6">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.document class="size-[18px] text-gray-400 dark:text-zinc-400" />
                        @if ($website->pages_fetched)
                            <a href="{{ route('pages.listing', $website['id'] ?? null) }}"
                                class="text-lg font-medium hover:underline" wire:navigate>Pages</a>
                        @else
                            <h3 class="text-lg font-medium">Pages</h3>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="fetchPages" wire:loading.attr="disabled"
                            :disabled="!$website->sitemaps_fetched || $fetchingPages || $website->pages_processing"
                            size="sm" class="cursor-pointer">
                            <span class="flex items-center gap-1">
                                <flux:icon.arrow-path
                                    class="size-4 {{ $fetchingPages || $website->pages_processing ? 'animate-spin' : '' }}" />
                                {{ $website->pages_fetched ? 'Refresh' : 'Fetch' }}
                            </span>
                        </flux:button>
                    </div>
                </div>

                {{-- Content --}}
                @if ($fetchingPages || $website->pages_processing)
                    <div class="flex items-center gap-3">
                        <flux:icon.arrow-path class="size-8 text-blue-400 animate-spin" />
                        <span class="text-sm text-gray-400 dark:text-zinc-400">Fetching pages...</span>
                    </div>
                @else
                    <div class="text-5xl mb-2">{{ $website['pages_count'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-zinc-500">Total pages</div>
                    @if (!empty($website['pages_last_sync']))
                        <div class="text-xs text-gray-400 dark:text-zinc-600 mt-1"
                            title="{{ $website->pages_last_sync }}">
                            Last synced: {{ $website->pages_last_sync->diffForHumans() }}
                        </div>
                    @endif
                    @if (!$website->sitemaps_fetched)
                        <p class="text-sm text-gray-500 dark:text-zinc-500 mt-2">
                            Fetch sitemaps first
                        </p>
                    @endif
                @endif

            </div>

        </div>

        {{-- ================== SITEMAP URLS TABLE ================== --}}
        <div class="flex flex-col rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-6 pt-6 pb-3 border-b border-gray-200 dark:border-zinc-800">
                <h3 class="font-semibold">Sitemap URLs</h3>
            </div>
            <div class="overflow-auto">
                <div class="p-6">
                    <table class="w-full text-sm table-fixed">
                        <thead class="text-left text-gray-500 dark:text-zinc-400">
                            <tr class="border-b border-gray-200 dark:border-zinc-800">
                                <th class="pb-2 pr-4">URL</th>
                                <th class="pb-2 pr-4">Created</th>
                                <th class="pb-2 pr-4">Updated</th>
                                <th class="pb-2 pr-4">Last modified</th>
                            </tr>
                        </thead>
                    </table>

                    <div class="overflow-y-auto" style="max-height: 400px;">
                        <table class="w-full text-sm table-fixed">
                            <tbody>
                                @if ($fetchingSitemaps || $website->sitemaps_processing)
                                    @for ($i = 0; $i < 5; $i++)
                                        <tr class="border-b border-gray-200 dark:border-zinc-800/50">
                                            <td class="py-3 pr-4">
                                                <div
                                                    class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-3/4">
                                                </div>
                                            </td>
                                            <td class="py-3 pr-4">
                                                <div
                                                    class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-24">
                                                </div>
                                            </td>
                                            <td class="py-3 pr-4">
                                                <div
                                                    class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-24">
                                                </div>
                                            </td>
                                            <td class="py-3 pr-4">
                                                <div
                                                    class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-24">
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                @else
                                    @forelse ($sitemaps as $sitemap)
                                        <tr
                                            class="border-b border-gray-200 hover:bg-gray-50 dark:border-zinc-800/50 dark:hover:bg-zinc-800/30 text-gray-700 dark:text-zinc-300">
                                            <td class="py-3 pr-4">
                                                <a href="{{ $sitemap->url }}" target="_blank"
                                                    rel="noopener noreferrer" class="hover:underline truncate block">
                                                    {{ $sitemap->url }}
                                                </a>
                                            </td>
                                            <td class="py-3 pr-4 text-gray-400 dark:text-zinc-400">
                                                {{ $sitemap->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="py-3 pr-4 text-gray-400 dark:text-zinc-400">
                                                {{ $sitemap->updated_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="py-3 pr-4 text-gray-400 dark:text-zinc-400">
                                                {{ $sitemap->lastmod?->format('Y-m-d H:i') ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4"
                                                class="text-center py-8 text-gray-500 dark:text-zinc-500">
                                                No sitemaps found
                                            </td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $sitemaps->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
