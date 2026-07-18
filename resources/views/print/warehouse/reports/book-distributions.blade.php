<x-print-layout :title="__('تقرير إحصائيات توزيع الكُتب المدرسية')" :printed-by="$printedBy">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير إحصائيات توزيع الكُتب المدرسية')"
            :colspan="5"
            :organization-lines="array_filter([$school->monitor?->name, $school->name])"
        >
            <x-slot:headerRight>
                <span>{{ __('عدد الصفوف الدراسية') }}:</span>
                <span class="font-mono">{{ $statistics->count() }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('الصف الدراسي') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
                <th scope="col" class="text-center">{{ __('المُوزَّع') }}</th>
                <th scope="col" class="text-center">{{ __('المُعلَّق') }}</th>
            </x-slot:columns>

            @forelse ($statistics as $statistic)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $statistic['name'] }}</td>
                    @if ($statistic['already_distributed'])
                        <td class="text-center font-mono">{{ $statistic['students_count'] }}</td>
                        <td class="text-center font-mono">{{ $statistic['distributed_count'] }}</td>
                        <td class="text-center font-mono">{{ $statistic['pending_count'] }}</td>
                    @else
                        <td colspan="3" class="text-center text-black/80">
                            {{ __('بانتظار تأكيد استلام الكُتب من المخزن') }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="print-empty-cell">
                        <x-empty-state class="justify-center" />
                    </td>
                </tr>
            @endforelse

            @if ($statistics->isNotEmpty())
                <tr class="print-row-highlight">
                    <td colspan="2" class="text-center font-semibold">{{ __('المجموع') }}</td>
                    <td class="text-center font-mono font-semibold">{{ $totals['students_count'] }}</td>
                    <td class="text-center font-mono font-semibold">{{ $totals['distributed_count'] }}</td>
                    <td class="text-center font-mono font-semibold">{{ $totals['pending_count'] }}</td>
                </tr>
            @endif
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
