<div @if ($testStatus === 'pending') wire:poll.2s="pollTestResult" @endif>
    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 p-6">
        <h3 class="text-lg font-medium mb-4">Test for Changes</h3>
        <p class="text-sm text-gray-500 dark:text-zinc-400 mb-4">
            Check if the website's XML sitemaps have been updated since the last scan.
        </p>

        <button wire:click="testForChanges" @disabled($testStatus === 'pending')
            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2 text-sm
                {{ $testStatus === 'pending' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
            <flux:icon.arrow-path class="size-4 {{ $testStatus === 'pending' ? 'animate-spin' : '' }}" />
            {{ $testStatus === 'pending' ? 'Testing...' : 'Test website' }}
        </button>

        @if ($testResult)
            @if ($testResult['error'] ?? null)
                {{-- Error state --}}
                <div class="mt-4 p-4 rounded-lg border bg-red-500/10 border-red-500/30">
                    <div class="text-sm text-red-400">{{ $testResult['error'] }}</div>
                </div>
            @else
                {{-- Summary panel --}}
                <div
                    class="mt-4 p-4 rounded-lg border
                    {{ $testResult['hasChanges'] ? 'bg-yellow-500/10 border-yellow-500/30' : 'bg-green-500/10 border-green-500/30' }}">
                    <div
                        class="font-medium mb-2
                        {{ $testResult['hasChanges'] ? 'text-yellow-400' : 'text-green-400' }}">
                        {{ $testResult['message'] }}
                    </div>
                    <ul class="text-sm text-gray-500 dark:text-zinc-400 space-y-1">
                        @foreach ($testResult['details'] as $detail)
                            <li>• {{ $detail['label'] }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Start new scan — always visible after a test --}}
                <button wire:click="start"
                    class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2 text-sm cursor-pointer">
                    <flux:icon.play class="size-3.5" />
                    Start new scan
                </button>

                {{-- Diff details — one group per detail entry that has items --}}
                @php
                    $hasAnyItems = collect($testResult['details'])->some(fn($d) => !empty($d['items']));
                @endphp

                @if ($hasAnyItems)
                    <div class="mt-6 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-zinc-300">Diff Details</h4>

                        @foreach ($testResult['details'] as $detail)
                            @if (!empty($detail['items']))
                                <div>
                                    <div class="text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">
                                        {{ $detail['label'] }}
                                    </div>
                                    <div
                                        class="rounded-lg border border-gray-100 dark:border-zinc-800 divide-y divide-gray-100 dark:divide-zinc-800 max-h-48 overflow-y-auto">
                                        @foreach ($detail['items'] as $item)
                                            <div class="flex items-center justify-between gap-4 px-3 py-1.5">
                                                <span
                                                    class="font-mono text-xs text-gray-600 dark:text-zinc-400 truncate min-w-0"
                                                    title="{{ $item['url'] }}">
                                                    {{ $item['url'] }}
                                                </span>
                                                @if (isset($item['old_lastmod']))
                                                    <div
                                                        class="flex items-center gap-1.5 flex-shrink-0 text-xs text-gray-400 dark:text-zinc-500 whitespace-nowrap">
                                                        <span>{{ $item['old_lastmod'] }}</span>
                                                        <flux:icon.arrow-right class="size-3" />
                                                        <span
                                                            class="text-blue-500 dark:text-blue-400">{{ $item['new_lastmod'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
