<div class="space-y-6">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('websites.listing') }}" wire:navigate class="hover:underline">Websites
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $website->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <x-page-header :name="$website->name" :url="$website->url">
        <flux:dropdown>
            <flux:button icon:trailing="cog-6-tooth" class="cursor-pointer"></flux:button>

            <flux:menu>
                <flux:modal.trigger name="website-modal">
                    <flux:menu.item
                        wire:click="dispatch('open-website-modal', {mode: 'edit', website: {{ $website }}})"
                        icon="pencil-square" class="cursor-pointer">Edit</flux:menu.item>
                </flux:modal.trigger>

                <flux:modal.trigger name="delete-website">
                    <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">Delete</flux:menu.item>
                </flux:modal.trigger>
            </flux:menu>
        </flux:dropdown>
    </x-page-header>

    {{-- Create/Edit website modal --}}
    <livewire:websites.modal />

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

    {{-- Website metadata --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Image --}}
        <div>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- ================== SITEMAPS CARD ================== --}}
        <x-card class="{{ !$website->sitemaps_fetched ? 'opacity-60' : '' }}">

            {{-- Header --}}
            <div class="px-6 pt-6 flex items-center space-x-2">
                <flux:icon.map class="size-4" />
                @if ($website->sitemaps_fetched)
                    <a href="{{ route('sitemaps.listing', $website['id'] ?? null) }}"
                        class="text-left font-semibold hover:underline" wire:navigate>
                        Sitemaps
                    </a>
                @else
                    <span class="font-semibold">
                        Sitemaps
                    </span>
                @endif
            </div>

            {{-- Content --}}
            <div class="px-6 pb-6 pt-4 space-y-4">

                @if (!$website->sitemaps_fetched)
                    {{-- Not fetched --}}
                    <div class="space-y-4">
                        <div class="text-center py-4">
                            <div class="text-3xl text-blue-600 mb-1">
                                0
                            </div>
                            <p class="text-sm">
                                Sitemaps not fetched
                            </p>
                        </div>

                        <flux:button wire:click="fetchSitemaps" wire:loading.attr="disabled"
                            :disabled="$fetchingSitemaps"
                            class="w-full inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium text-white transition cursor-pointer">
                            @if ($fetchingSitemaps)
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    <span>
                                        Fetching sitemaps…
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrows-right-left class="size-4" />
                                    <span>
                                        Fetch sitemaps
                                    </span>
                                </div>
                            @endif
                        </flux:button>

                    </div>
                @else
                    {{-- Fetched --}}
                    <div class="space-y-4">

                        <div class="text-center py-4">
                            <div class="text-3xl text-blue-600 mb-1">
                                {{ $website['sitemaps_count'] ?? 0 }}
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ ($website['sitemaps_count'] ?? 0) === 1 ? 'Sitemap' : 'Sitemaps' }} found
                            </p>
                        </div>

                        <flux:button wire:click="fetchSitemaps" wire:loading.attr="disabled"
                            :disabled="$fetchingSitemaps"
                            class="w-full inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium text-white transition cursor-pointer">
                            @if ($fetchingSitemaps)
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    <span>
                                        Refreshing sitemaps…
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4" />
                                    <span>
                                        Refresh sitemaps
                                    </span>
                                </div>
                            @endif
                        </flux:button>

                        @if (!empty($website['sitemaps_last_sync']))
                            <p class="text-xs text-gray-500 text-center pt-2 border-t border-gray-200"
                                title="{{ $website->sitemaps_last_sync }}">
                                Last synced: {{ $website->sitemaps_last_sync->diffForHumans() }}
                            </p>
                        @endif

                    </div>
                @endif

                {{-- Add sitemap manually --}}

                <flux:modal.trigger name="add-sitemap">
                    <flux:button icon="plus" class="cursor-pointer w-full">
                        Add sitemap
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="add-sitemap" class="md:w-96">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Add new sitemap</flux:heading>
                        </div>

                        <flux:input wire:model.defer="sitemapUrl" label="Sitemap"
                            placeholder="https://example.com/sitemap.xml" />

                        <div class="flex">
                            <flux:spacer />
                            <flux:button wire:click="addSitemap" wire:loading.attr="disabled" type="submit"
                                variant="primary" class="cursor-pointer">Save changes
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>


                @if ($website['sitemaps_message'] !== 'ok')
                    <p class="text-xs text-center pt-2">
                        {{ $website->sitemaps_message }}
                    </p>
                @endif

            </div>
        </x-card>

        {{-- ================== PAGES CARD ================== --}}
        <x-card class="{{ !$website->pages_fetched ? 'opacity-60' : '' }}">

            {{-- Header --}}
            <div class="px-6 pt-6 flex items-center space-x-2">
                <flux:icon.document class="size-4" />
                @if ($website->pages_fetched)
                    <a href="{{ route('pages.listing', $website['id'] ?? null) }}"
                        class="text-left font-semibold hover:underline" wire:navigate>
                        Pages
                    </a>
                @else
                    <span class="font-semibold">
                        Pages
                    </span>
                @endif
            </div>

            {{-- Content --}}
            <div class="px-6 pb-6 pt-4">

                @if (!$website->pages_fetched)
                    {{-- Not fetched --}}
                    <div class="space-y-4">

                        <div class="text-center py-4">
                            <div class="text-3xl text-blue-600 mb-1">
                                0
                            </div>
                            <p class="text-sm">
                                Pages not fetched
                            </p>
                        </div>

                        <flux:button wire:click="fetchPages" wire:loading.attr="disabled"
                            :disabled="!$website->sitemaps_fetched || $fetchingPages || $website->pages_processing"
                            class="w-full inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium text-white transition cursor-pointer">
                            @if ($fetchingPages || $website->pages_processing)
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    <span>
                                        Fetching pages…
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrows-right-left class="size-4" />
                                    <span>
                                        Fetch pages
                                    </span>
                                </div>
                            @endif
                        </flux:button>

                        @if (!$website->sitemaps_fetched)
                            <p class="text-sm text-center">
                                Fetch sitemaps first
                            </p>
                        @endif
                    </div>
                @else
                    {{-- Fetched --}}
                    <div class="space-y-4">

                        <div class="text-center py-4">
                            <div class="text-3xl text-blue-600 mb-1">
                                {{ $website['pages_count'] ?? 0 }}
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ ($website['pages_count'] ?? 0) === 1 ? 'Page' : 'Pages' }} found
                            </p>
                        </div>

                        <flux:button wire:click="fetchPages" wire:loading.attr="disabled"
                            :disabled="!$website->sitemaps_fetched || $fetchingPages || $website->pages_processing"
                            class="w-full inline-flex items-center justify-center h-9 rounded-md px-4 text-sm font-medium text-white transition cursor-pointer">
                            @if ($fetchingPages || $website->pages_processing)
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    <span>
                                        Refreshing pages…
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <flux:icon.arrow-path class="size-4" />
                                    <span>
                                        Refresh pages
                                    </span>
                                </div>
                            @endif
                        </flux:button>

                        @if (!empty($website['pages_last_sync']))
                            <p class="text-xs text-gray-500 text-center pt-2 border-t border-gray-200"
                                title="{{ $website->pages_last_sync }}">
                                Last synced: {{ $website->pages_last_sync->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                @endif


            </div>
        </x-card>

        {{-- ================== CONTENT CARD ================== --}}
        <livewire:content-extraction.status-card :website="$website" />

    </div>

    @if (session('status'))
        <div class="px-4 py-3  rounded bg-green-100 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <livewire:content-extraction.run-overview :website="$website" />

</div>
