<x-print-layout :title="__('تقرير المدارس')" :printed-by="auth('administration')->user()->name">
    <x-slot:content>
        <x-print.report-table :title="__('تقرير المدارس')" :colspan="6">
            <x-slot:headerRight>
                <span>{{ __('إجمالي المدارس') }}:</span>
                <span class="font-mono">{{ $schools->count() }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('المدرسة') }}</th>
                <th scope="col" class="text-center">{{ __('نوع المدرسة') }}</th>
                <th scope="col" class="text-center">{{ __('الفترة الدراسية') }}</th>
                <th scope="col" class="text-center">{{ __('المُراقبة') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($schools as $school)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $school->name }}</td>
                    <td class="text-center">{{ $school->type->label() }}</td>
                    <td class="text-center">{{ $school->academic_period->label() }}</td>
                    <td class="text-center">{{ $school->monitor?->name }}</td>
                    <td class="text-center font-mono">{{ $school->students_count ?? 0 }}</td>
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
