<x-print-layout :title="__('تقرير المدارس')" :printed-by="auth('administration')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير المدارس')"
            :colspan="6"
        >
            <x-slot:headerRight>
                <span>{{ __('إجمالي المدارس') }}:</span>
                <span class="font-mono">{{ $schools->count() }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('المدرسة') }}</th>
                <th scope="col">{{ __('نوع المدرسة') }}</th>
                <th scope="col">{{ __('الفترة الدراسية') }}</th>
                <th scope="col">{{ __('المُراقبة') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($schools as $school)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $school->name }}</td>
                    <td>{{ $school->type->label() }}</td>
                    <td>{{ $school->academic_period_label }}</td>
                    <td>{{ $school->monitor?->name }}</td>
                    <td class="text-center font-mono">{{ $school->periods->sum('students_count') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="print-empty-cell">
                        <x-empty-state class="justify-center" />
                    </td>
                </tr>
            @endforelse
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
