<div class="max-w-2xl mx-auto p-4">

    <!-- Reindex -->
    <div class="flex items-center justify-between mb-3">
        <flux:button wire:click="reindex" wire:loading.attr="disabled" wire:target="reindex"
            variant="ghost" size="sm" icon="arrow-path" class="cursor-pointer">
            <span wire:loading.remove wire:target="reindex">Reindex everything</span>
            <span wire:loading wire:target="reindex">Reindexing…</span>
        </flux:button>

        @if ($reindexStatus)
            <span class="text-sm {{ $reindexError ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                {{ $reindexStatus }}
            </span>
        @endif
    </div>

    <!-- Search Input -->
    <flux:input wire:model.live.debounce.300ms="query" placeholder="Search content..."
        class="w-full  focus:outline-none focus:ring" />

    <!-- Results -->
    <div class="mt-4 space-y-4">
        @if ($results)
            @forelse ($results as $result)
                <a href="{{ $result['websiteId'] ? route('pages.listing', ['website' => $result['websiteId']]) . '?search=' . urlencode($result['canonicalPath'] ?? '') : '#' }}"
                    class="block p-4 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">

                    @if ($result['title'])
                        <div class="font-semibold text-gray-900 dark:text-white [&_em]:bg-yellow-200 [&_em]:dark:bg-yellow-800 [&_em]:not-italic [&_em]:rounded-sm">
                            {!! $result['title'] !!}
                        </div>
                    @endif

                    @if ($result['description'])
                        <div class="text-sm text-gray-600 dark:text-zinc-400 mt-1 [&_em]:bg-yellow-200 [&_em]:dark:bg-yellow-800 [&_em]:not-italic [&_em]:rounded-sm">
                            {!! $result['description'] !!}
                        </div>
                    @endif

                    @if ($result['bodySnippet'])
                        <div class="text-sm text-gray-600 dark:text-zinc-400 mt-2 pl-2 border-l-2 border-gray-300 dark:border-zinc-600 [&_em]:bg-yellow-200 [&_em]:dark:bg-yellow-800 [&_em]:not-italic [&_em]:rounded-sm">
                            {!! $result['bodySnippet'] !!}
                        </div>
                    @endif

                    @if ($result['canonical'])
                        <div class="text-xs text-blue-500 dark:text-blue-400 mt-2 truncate">{{ $result['canonical'] }}</div>
                    @endif

                </a>
            @empty
                <div class="text-gray-500 dark:text-zinc-400">No results found.</div>
            @endforelse

            @if ($results->hasPages())
                <div class="mt-4">
                    {{ $results->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
