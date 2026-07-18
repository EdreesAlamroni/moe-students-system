<x-print-layout :title="__('تقرير المُراقبات')" :printed-by="auth('administration')->user()->name">
    <x-slot:content>
        <x-print.report-table :title="__('تقرير المُراقبات')" :colspan="5">
            <x-slot:headerRight>
                <span>{{ __('إجمالي المُراقبات') }}:</span>
                <span class="font-mono">{{ $count->count() }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('اسم المُراقبة') }}</th>
                <th scope="col" class="text-center">{{ __('عدد مكاتب الخدمات التعليمية') }}</th>
                <th scope="col" class="text-center">{{ __('عدد المدارس') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($monitors as $monitor)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $monitor->name }}</td>
                    <td class="text-center font-mono">{{ $monitor->offices_count ?? 0 }}</td>
                    <td class="text-center font-mono">{{ $monitor->schools_count ?? 0 }}</td>
                    <td class="text-center font-mono">{{ $monitor->students_count ?? 0 }}</td>
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
