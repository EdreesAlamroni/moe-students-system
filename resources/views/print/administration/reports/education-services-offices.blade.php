<x-print-layout :title="__('تقرير مكاتب الخدمات التعليمية')" :printed-by="auth('administration')->user()->name">
    <x-slot:content>
        <x-print.report-table :title="__('تقرير مكاتب الخدمات التعليمية')" :colspan="5">
            <x-slot:headerRight>
                <span>{{ __('إجمالي مكاتب الخدمات') }}:</span>
                <span class="font-mono">{{ $count }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('مكتب الخدمات التعليمية') }}</th>
                <th scope="col">{{ __('المُراقبة') }}</th>
                <th scope="col" class="text-center">{{ __('عدد المدارس') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($offices as $office)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $office->name }}</td>
                    <td>{{ $office->monitor?->name }}</td>
                    <td class="text-center font-mono">{{ $office->schools_count ?? 0 }}</td>
                    <td class="text-center font-mono">{{ $office->students_count ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="print-empty-cell">
                        <x-empty-state class="justify-center" />
                    </td>
                </tr>
            @endforelse
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
