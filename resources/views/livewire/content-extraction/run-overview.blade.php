        <x-card>
            <div class="p-6" @if ($isProcessing) wire:poll.2s @endif>

                <h3 class="text-lg mb-6 font-medium">Content Extraction</h3>

                @if ($run)
                    {{-- Progress Section --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-zinc-400">
                                Processing {{ $run->processed_pages }} of {{ $run->total_pages }} pages
                            </span>
                            <span class="text-sm text-zinc-400">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-zinc-800 rounded-full h-2 overflow-hidden">
                            <div class="h-full transition-all duration-500 bg-yellow-500"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- Live Log --}}
                    <div class="mb-6">
                        <div class="text-sm text-zinc-500 mb-2">Live Crawl Log</div>
                        <div
                            class="bg-zinc-950 rounded-lg border border-zinc-800 p-4 h-64 overflow-y-auto font-mono text-xs scroll-smooth">
                            @forelse($events as $event)
                                <div class="py-1 text-zinc-400">
                                    <span class="text-zinc-600 mr-2">
                                        {{ \Carbon\Carbon::parse($event['timestamp'])->format('g:i:s A') }}
                                    </span>
                                    {{ $event['message'] ?? ($event['type'] ?? 'Processing...') }}
                                </div>
                            @empty
                                <div class="text-zinc-600">Waiting for logs...</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Page Status Table --}}
                    <div>
                        <div class="text-sm text-zinc-500 mb-2">Page Status</div>
                        <div class="bg-zinc-950 rounded-lg border border-zinc-800 overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 bg-zinc-900 border-b border-zinc-800">
                                        <tr>
                                            <th class="text-left px-4 py-3 text-zinc-400">URL</th>
                                            <th class="text-left px-4 py-3 text-zinc-400 w-32">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($run->pageExtractions as $ticket)
                                            <tr
                                                class="border-b border-zinc-800/50 hover:bg-zinc-900/50 transition-colors">
                                                <td class="px-4 py-2 text-zinc-300 font-mono text-xs truncate max-w-md"
                                                    title="{{ $ticket->page->url }}">
                                                    {{ $ticket->page->url }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded text-xs 
                                            {{ $ticket->status->value === 'done' ? 'bg-green-900/30 text-green-400' : '' }}
                                            {{ $ticket->status->value === 'failed' ? 'bg-red-900/30 text-red-400' : '' }}
                                            {{ $ticket->status->value === 'processing' ? 'bg-yellow-900/30 text-yellow-400' : '' }}
                                            {{ $ticket->status->value === 'pending' ? 'bg-zinc-800 text-zinc-400' : '' }}">
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
                    <div class="text-center py-12 text-zinc-500">
                        No extraction runs found for this website.
                    </div>
                @endif
            </div>
        </x-card>
