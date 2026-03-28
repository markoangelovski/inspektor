@props([
    'class' => '',
])

<div data-slot="card"
    {{ $attributes->merge([
        'class' => "text-card-foreground flex flex-col gap-6 rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden $class",
    ]) }}>
    {{ $slot }}
</div>
