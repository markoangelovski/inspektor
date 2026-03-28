@props([
    'url' => '',
    'name' => '',
])

<div class="mb-8 flex items-center justify-between gap-4">

    {{-- Left: Website info --}}
    <div class="flex items-center gap-4 min-w-0">
        <img src="https://www.google.com/s2/favicons?domain={{ $url }}&sz=64" alt=""
            class="w-10 h-10 rounded-lg object-contain flex-shrink-0" />

        <div class="min-w-0">
            <h1 class="mb-1 truncate">
                {{ $name }}
            </h1>

            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-blue-600 hover:underline truncate block">
                {{ $url }}
            </a>
        </div>
    </div>

    {{ $slot }}

</div>
