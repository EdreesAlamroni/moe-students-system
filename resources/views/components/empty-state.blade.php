@props([
    'text' => __('لا يوجد محتوى متاح للعرض.'),
    'textFilter' => __('لا توجد نتائج مطابقة للبحث.'),
    'hasFilter' => request()->has('filter'),
])

<div {!! $attributes->merge(['class' => 'flex items-center text-gray-500 text-sm gap-x-1.5 font-medium']) !!}>
    <x-heroicon-s-information-circle class="w-5 h-5" />
    @if ($hasFilter)
        <span>{{ $textFilter }}</span>
    @else
        <span>{{ $text }}</span>
    @endif
</div>
