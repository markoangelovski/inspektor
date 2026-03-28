@php
    $statusValue = is_string($status) ? $status : $status->value;
    $isProcessing = in_array($statusValue, ['running', 'pending', 'cancelling']);
@endphp

<div @if ($isProcessing) wire:poll.2s="refresh" @endif>
    <x-card class="h-full {{ !$website->pages_fetched ? 'opacity-60' : '' }}">
        {{-- Header --}}
        <div class="px-6 pt-6 flex items-center space-x-2">
            <flux:icon.circle-stack class="size-4" />
            <span class="font-semibold">Content</span>
        </div>

        {{-- Content --}}
        <div class="px-6 pb-6 pt-4 space-y-4">

            {{-- Stats Display --}}
            <div class="text-center py-4">
                <div class="text-3xl text-blue-600 mb-1">
                    {{ $processed }}/{{ $total ?: $website->pages()->count() }}
                </div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Pages Processed</p>

                @if ($statusValue !== 'idle')
                    <div class="mt-2">
                        <flux:badge size="sm"
                            :variant="match($statusValue) {
                                'running' => 'success',
                                'paused' => 'warning',
                                'completed' => 'neutral',
                                'failed', 'cancelled' => 'danger',
                                default => 'neutral'
                            }">
                            {{ strtoupper($statusValue) }}
                        </flux:badge>
                    </div>
                @endif
            </div>

            {{-- Error Summary --}}
            @if ($website->latestRun?->failure_summary)
                <div class="p-2 text-xs bg-red-50 border border-red-100 text-red-600 rounded">
                    {{ $website->latestRun->failure_summary }}
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="space-y-2">
                @if (in_array($statusValue, ['idle', 'completed', 'cancelled', 'failed']))
                    <flux:button wire:click="start" class="w-full cursor-pointer" icon="play">
                        Start
                    </flux:button>
                @elseif(in_array($statusValue, ['running', 'pending']))
                    <flux:button wire:click="pause" class="w-full cursor-pointer" icon="pause">
                        Pause
                    </flux:button>
                @elseif($statusValue === 'paused')
                    <flux:button wire:click="resume" class="w-full cursor-pointer" icon="play">
                        Resume
                    </flux:button>
                @endif

                @if ($statusValue !== 'idle')
                    <flux:button wire:click="restart" variant="subtle" class="w-full cursor-pointer" icon="arrow-path">
                        Restart
                    </flux:button>
                @endif
            </div>

            {{-- Footer Sync Info --}}
            @if ($lastSynced)
                <p class="text-xs text-gray-500 text-center pt-2 border-t border-gray-200">
                    Last synced: {{ $lastSynced }}
                </p>
            @endif
        </div>
    </x-card>
</div>
