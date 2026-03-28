@props([
    'class' => '',
])

<div data-slot="card-content"
    {{ $attributes->merge([
        'class' => "px-6 pt-6 [&:last-child]:pb-6 $class",
    ]) }}>
    {{ $slot }}
</div>
