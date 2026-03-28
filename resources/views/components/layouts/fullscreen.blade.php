<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Information Architecture' }}</title>


    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @livewireStyles
    @fluxAppearance
</head>

<body class="h-full font-sans antialiased bg-white dark:bg-zinc-900">
    {{-- This is where the content from livewire.pages.expand will be injected --}}
    {{ $slot }}

    @livewireScripts
</body>

</html>
