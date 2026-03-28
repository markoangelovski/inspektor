<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Websites') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage websites') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="text-end mb-4">
        <flux:modal.trigger name="website-modal">
            <flux:button wire:click="dispatch('open-website-modal', {mode: 'create'})" variant="primary"
                icon="plus-circle" class="cursor-pointer">Add Website</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Create website modal --}}
    <livewire:websites.modal />

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        @forelse ($websites as $website)
            {{-- Website card --}}
            <a href="{{ route('websites.detail', $website->id) }}" wire:navigate>
                <div
                    class="group cursor-pointer overflow-hidden hover:shadow-lg transition-shadow relative bg-white rounded-lg border border-gray-200">

                    {{-- Meta image --}}
                    <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                        @php
                            $isMetaImage = !empty($website['meta_image_url']);
                        @endphp
                        <img src="{{ $isMetaImage
                            ? $website['meta_image_url']
                            : 'https://www.google.com/s2/favicons?domain=' . $website['url'] . '&sz=64' }}"
                            class="
                        absolute inset-0 w-full h-full
                        {{ $isMetaImage ? 'object-cover' : 'object-contain p-8' }}
                    "
                            alt="" />

                        {{-- Hover overlay --}}
                        <div
                            class="absolute inset-0 bg-black/80 p-4 flex items-center justify-center
                                opacity-0 group-hover:opacity-100 transition">
                            <p class="text-white text-sm text-center line-clamp-4">
                                {{ $website['meta_description'] }}
                            </p>
                        </div>
                    </div>


                    {{-- Card content --}}
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <img src="https://www.google.com/s2/favicons?domain={{ $website['url'] }}&sz=32"
                                class="w-6 h-6 rounded object-contain mt-1" />

                            <div class="flex-1 min-w-0">
                                <h3 class="text-gray-900 line-clamp-2 mb-1">
                                    {{ $website['name'] }}
                                </h3>

                                <p class="text-sm text-gray-500 truncate">
                                    {{ $website['url'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

        @empty
            <p>No websites available.</p>
        @endforelse

    </div>

    <div class="mt-4">
        {{ $websites->links() }}
    </div>

</div>
