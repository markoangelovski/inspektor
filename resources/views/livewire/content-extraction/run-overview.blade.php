<div @if ($isProcessing) wire:poll.2s @endif>
    <x-card>
        {{-- Header --}}
        <div class="px-6 pt-6 flex items-center space-x-2">
            <flux:icon.clock class="size-4" />
            <span class="font-semibold">Scan History</span>
        </div>

        @if ($runs->isEmpty())
            <div class="px-6 pb-6 pt-4 text-center py-12 text-gray-500 dark:text-zinc-500">
                No extraction runs found for this website.
            </div>
        @else
            <div class="flex mt-4 border-t border-gray-100 dark:border-zinc-800" style="min-height: 600px;">

                {{-- Left Sidebar: run list --}}
                <div class="w-64 flex-shrink-0 border-r border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 overflow-y-auto">
                    <div class="p-2 space-y-1">
                        @foreach ($runs as $r)
                            @php
                                $rStatus = is_string($r->status) ? $r->status : $r->status->value;
                            @endphp
                            <button
                                wire:click="selectRun('{{ $r->id }}')"
                                class="w-full text-left p-3 rounded-lg transition-colors
                                    {{ $selectedRunId === $r->id
                                        ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-zinc-100'
                                        : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50 hover:text-gray-900 dark:hover:text-zinc-300' }}"
                            >
                                <div class="flex items-center justify-between mb-1 gap-2">
                                    <span class="text-sm font-medium truncate">{{ $r->created_at->format('M j, Y') }}</span>
                                    <flux:badge size="sm"
                                        :variant="match($rStatus) {
                                            'running' => 'success',
                                            'paused' => 'warning',
                                            'completed' => 'neutral',
                                            'completed_with_errors' => 'warning',
                                            'failed' => 'danger',
                                            default => 'neutral'
                                        }">
                                        {{ str_replace('_', ' ', $rStatus) }}
                                    </flux:badge>
                                </div>
                                <div class="text-xs text-gray-400 dark:text-zinc-500">
                                    {{ $r->processed_pages }} / {{ $r->total_pages }} pages
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Right Content: selected run details --}}
                <div class="flex-1 min-w-0 p-6">
                    @if ($run)
                        @php
                            $runStatus = is_string($run->status) ? $run->status : $run->status->value;
                        @endphp

                        {{-- Run Header --}}
                        <div class="flex items-start justify-between mb-6 gap-4">
                            <div>
                                <h4 class="font-medium mb-1">
                                    {{ $run->created_at->format('l, F j, Y') }}
                                </h4>
                                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-zinc-400">
                                    <span>{{ $run->processed_pages }} pages processed</span>
                                    <span>•</span>
                                    <span class="{{ match($runStatus) {
                                        'running' => 'text-green-600 dark:text-green-400',
                                        'paused' => 'text-amber-600 dark:text-yellow-400',
                                        'completed' => 'text-gray-600 dark:text-zinc-400',
                                        'completed_with_errors' => 'text-amber-600 dark:text-yellow-400',
                                        'failed' => 'text-red-600 dark:text-red-400',
                                        default => 'text-gray-600 dark:text-zinc-400'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $runStatus)) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Per-run controls --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if (in_array($runStatus, ['running', 'pending']))
                                    <flux:button wire:click="pause" size="sm" class="cursor-pointer" icon="pause">
                                        Pause
                                    </flux:button>
                                @elseif ($runStatus === 'paused')
                                    <flux:button wire:click="resume" size="sm" class="cursor-pointer" icon="play">
                                        Resume
                                    </flux:button>
                                    <flux:button wire:click="restart" size="sm" variant="subtle" class="cursor-pointer" icon="arrow-path">
                                        Restart
                                    </flux:button>
                                @elseif (in_array($runStatus, ['completed', 'completed_with_errors', 'failed']))
                                    <flux:button wire:click="restart" size="sm" class="cursor-pointer" icon="arrow-path">
                                        Restart
                                    </flux:button>
                                @endif
                            </div>
                        </div>

                        {{-- Progress Bar (only when processing) --}}
                        @if ($isProcessing)
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-500 dark:text-zinc-400">
                                        Processing {{ $run->processed_pages }} of {{ $run->total_pages }} pages
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-zinc-400">{{ $progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                    <div class="h-full transition-all duration-500 bg-blue-500"
                                        style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Extraction Events Log --}}
                        <div class="mb-6">
                            <div class="text-sm text-gray-500 dark:text-zinc-500 mb-2">
                                {{ $isProcessing ? 'Live Crawl Log' : 'Extraction Events' }}
                            </div>
                            <div class="bg-gray-50 dark:bg-zinc-950 rounded-lg border border-gray-100 dark:border-zinc-800 p-4 max-h-64 overflow-y-auto font-mono text-xs">
                                @forelse ($events as $event)
                                    <div class="py-1 {{ match($event['type'] ?? '') {
                                        'page.done' => 'text-green-600 dark:text-green-400',
                                        'page.failed' => 'text-red-600 dark:text-red-400',
                                        default => 'text-gray-500 dark:text-zinc-400'
                                    } }}">
                                        <span class="text-gray-400 dark:text-zinc-600 mr-2">
                                            {{ \Carbon\Carbon::parse($event['timestamp'])->format('g:i:s A') }}
                                        </span>
                                        {{ $event['message'] ?? ($event['type'] ?? 'Processing...') }}
                                    </div>
                                @empty
                                    <div class="text-gray-400 dark:text-zinc-600">Waiting for logs...</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Page Status Table --}}
                        <div>
                            <div class="text-sm text-gray-500 dark:text-zinc-500 mb-2">Page Status</div>
                            <div class="bg-gray-50 dark:bg-zinc-950 rounded-lg border border-gray-100 dark:border-zinc-800 overflow-hidden">
                                <div class="max-h-64 overflow-y-auto">
                                    <table class="w-full text-sm">
                                        <thead class="sticky top-0 bg-gray-100 dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800">
                                            <tr>
                                                <th class="text-left px-4 py-3 text-gray-500 dark:text-zinc-400 font-medium">URL</th>
                                                <th class="text-left px-4 py-3 text-gray-500 dark:text-zinc-400 font-medium w-32">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($run->pageExtractions as $ticket)
                                                <tr class="border-b border-gray-100 dark:border-zinc-800/50 hover:bg-gray-100/50 dark:hover:bg-zinc-900/50 transition-colors">
                                                    <td class="px-4 py-2 text-gray-700 dark:text-zinc-300 font-mono text-xs truncate max-w-md"
                                                        title="{{ $ticket->page->url }}">
                                                        {{ $ticket->page->url }}
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs
                                                            {{ match($ticket->status->value) {
                                                                'done' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                                                'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                                                'processing' => 'bg-amber-100 dark:bg-yellow-900/30 text-amber-700 dark:text-yellow-400',
                                                                'skipped' => 'bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500',
                                                                default => 'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400'
                                                            } }}">
                                                            {{ $ticket->status->value }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="flex items-center justify-center h-full text-gray-400 dark:text-zinc-500" style="min-height: 300px;">
                            Select a scan to view details
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-card>
</div>
