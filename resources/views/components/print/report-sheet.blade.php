@props([
    'padding' => '5mm',
])

<section {{ $attributes->class(['sheet', "padding-{$padding}"]) }}>
    {{ $slot }}
</section>
