<div class="h-screen w-full flex flex-col" x-init="window.addEventListener('pages:view-content', (e) => $wire.openViewer(e.detail.pageId))">
    {{-- Optional Header Overlay --}}
    <div class="absolute top-4 left-4 z-50 flex items-center gap-3">
        <flux:button icon="arrow-left" variant="subtle" class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur"
            {{-- Pointing back to the main list --}} href="{{ route('pages.listing', $website) }}">
            Exit Fullscreen
        </flux:button>

        <div
            class="px-3 py-1.5 rounded-md bg-white/80 dark:bg-zinc-800/80 backdrop-blur border border-gray-200 dark:border-zinc-700 shadow-sm">
            <span class="text-sm font-medium text-gray-900 dark:text-zinc-100">
                {{ $website->name }}
            </span>
        </div>
    </div>

    {{-- React Flow Root - Must be h-full --}}
    <div wire:ignore id="pages-flow-root" class="flex-1 w-full" data-pages='@json($pages->values())'></div>

    {{-- The Reusable Drawer --}}
    <x-inspector.page-viewer-drawer :viewer-page="$viewerPage" />
</div>
