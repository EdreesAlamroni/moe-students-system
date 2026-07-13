@props([
    'title',
    'organizationName' => null,
    'organizationLines' => null,
])

@php
    $organizationLines = collect($organizationLines ?? (filled($organizationName) ? [$organizationName] : []))
        ->filter(fn (mixed $line): bool => filled($line))
        ->values()
        ->all();
@endphp

<section {{ $attributes->class(['print-header print-keep-together']) }}>
    <div class="print-header-side">
        <img
            src="{{ asset('assets/images/logo.png') }}"
            alt=""
            aria-hidden="true"
            class="print-header-logo"
        />

        <div class="print-header-meta print-header-right">
            {{ $right ?? '' }}
        </div>
    </div>

    <div class="print-header-center">
        <p class="print-ministry-title">
            {{ __('وزارة التربية والتعليم بالحكومة الليبية') }}
        </p>

        @if (count($organizationLines) > 0)
            <div class="print-header-organization">
                @foreach ($organizationLines as $line)
                    <div class="print-header-organization-line">{{ $line }}</div>
                @endforeach
            </div>
        @endif

        <div class="print-header-title-rule" aria-hidden="true"></div>

        <div @class([
            'print-header-title',
            'print-header-title--primary' => count($organizationLines) === 0,
        ])>
            {{ $title }}
        </div>
    </div>

    <div class="print-header-side">
        <img
            src="{{ asset('assets/images/logo.png') }}"
            alt=""
            aria-hidden="true"
            class="print-header-logo"
        />

        <div class="print-header-meta print-header-left">
            <span class="font-mono">{{ now()->format('Y-m-d h:i') }}</span>
            <span>{{ now()->locale('ar')->translatedFormat('a') }}</span>
        </div>
    </div>
</section>
