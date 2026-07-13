@props([
    'printedBy',
    'pageNumber' => null,
])

<section {{ $attributes->class(['print-footer print-keep-together']) }}>
    <div>
        <div>
            <span>{{ __('طبع بواسطة') }}:</span>
            <span>{{ $printedBy }}</span>
        </div>

        @if (filled($pageNumber))
            <div class="font-medium">
                <span>{{ __('الصفحة رقم') }}:</span>
                <span class="font-mono">{{ $pageNumber }}</span>
            </div>
        @endif
    </div>
</section>
