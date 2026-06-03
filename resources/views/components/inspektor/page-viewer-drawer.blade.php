{{-- resources/views/components/page-viewer-drawer.blade.php --}}
@props(['viewerPage'])

<div x-data="{ open: @entangle('viewerOpen') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex">
    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-black/50"></div>

    {{-- Panel --}}
    <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full" @keydown.escape.window="open = false"
        class="relative ml-auto h-full w-full max-w-2xl bg-white dark:bg-zinc-900 border-l border-gray-200 dark:border-zinc-800 flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-zinc-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-zinc-100">Page Content</h2>
            <button type="button" wire:click="closeViewer"
                class="text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 cursor-pointer">
                ✕
            </button>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-auto p-4 space-y-6 text-sm">
            @if ($viewerPage)
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">URL:</span>
                        <div class="mt-1 flex items-start gap-2">
                            <a href="{{ $viewerPage->url }}" target="_blank" rel="noopener noreferrer"
                                class="break-all text-gray-500 hover:text-blue-600 hover:underline min-w-0">
                                {{ $viewerPage->url }}
                            </a>
                            <div x-data="{ copied: false }"
                                class="shrink-0 mt-0.5">
                                <button type="button"
                                    title="Copy to clipboard"
                                    @click="navigator.clipboard.writeText('{{ $viewerPage->url }}'); copied = true; setTimeout(() => copied = false, 5000)"
                                    class="text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                                    <flux:icon.check x-show="copied" class="size-4 text-green-500" />
                                    <flux:icon.document-duplicate x-show="!copied" class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">Path:</span>
                        <p class="mt-1">{{ $viewerPage->path }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">Date Created:</span>
                        <p class="mt-1">{{ $viewerPage->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">Date Updated:</span>
                        <p class="mt-1">{{ $viewerPage->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">Last Modified:</span>
                        <p class="mt-1">{{ $viewerPage->lastmod?->format('Y-m-d H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-zinc-500">Originating Sitemap:</span>
                        @if ($viewerPage->sitemap)
                            <p class="mt-1 break-all">
                                <a href="{{ $viewerPage->sitemap->url }}" target="_blank" rel="noopener noreferrer"
                                    class="text-gray-500 hover:text-blue-600 hover:underline break-all">
                                    {{ $viewerPage->sitemap->url }}
                                </a>
                            </p>
                        @else
                            <p class="mt-1 text-gray-400 dark:text-zinc-500">—</p>
                        @endif
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-zinc-800">
                    <div class="mb-3 text-gray-500 dark:text-zinc-400">Extracted Content:</div>
                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-950 p-4 overflow-auto text-xs">
                        <div id="json-viewer-root" wire:ignore></div>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-full text-gray-500 dark:text-zinc-500">
                    No page selected
                </div>
            @endif
        </div>
    </div>
</div>
