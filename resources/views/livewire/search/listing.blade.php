<div class="max-w-2xl mx-auto p-4">

    <!-- Search Input -->
    <flux:input wire:model.live.debounce.300ms="query" placeholder="Search content..."
        class="w-full  focus:outline-none focus:ring" />

    <!-- Results -->
    <div class="mt-4 space-y-4">
        @forelse ($results as $result)
            <div class="p-4 border rounded-lg">

                <!-- Example: render content safely -->
                <div class="text-sm text-gray-700">
                    @if (is_array($result['content']))
                        {{ \Illuminate\Support\Str::limit(json_encode($result['content']), 600) }}
                    @else
                        {{ \Illuminate\Support\Str::limit($result['content'], 600) }}
                    @endif
                </div>

                <div class="text-xs text-gray-400 mt-2">
                    ID: {{ $result['id'] }}
                </div>

            </div>
        @empty
            @if (strlen($query) > 1)
                <div class="text-gray-500">
                    No results found.
                </div>
            @endif
        @endforelse
    </div>

</div>
