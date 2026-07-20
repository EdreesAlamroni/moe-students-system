<x-print-layout :landscape="true" :title="__('الجدول الدراسي')" :printed-by="auth('school')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('الجدول الدراسي')"
            :colspan="count($days) + 1"
            :organization-lines="$classroom->school->printOrganizationLines()"
        >
            <x-slot:headerRight>
                <span>{{ __('السنة الدراسية') }}:</span>
                <span class="font-mono">{{ $classroom->academicYear?->name ?? '-' }}</span>
            </x-slot:headerRight>

            <x-slot:meta>
                <div class="print-meta-bar-item-wide rounded-none">
                    <span>{{ __('الصف الدراسي') }}:</span>
                    <span class="font-medium">{{ $classroom->gradeLevel->name }}</span>
                </div>

                <div class="print-meta-bar-item-wide rounded-none">
                    <span>{{ __('الفصل') }}:</span>
                    <span class="font-medium font-mono">{{ $classroom->name }}</span>
                </div>
            </x-slot:meta>

            <x-slot:columns>
                <th scope="col" class="w-[16%] text-right">{{ __('الحصة') }}</th>
                @foreach ($days as $day)
                    <th scope="col" class="text-center">{{ $day->label() }}</th>
                @endforeach
            </x-slot:columns>

            @foreach ($schedule['periods'] as $period)
                <tr @class(['print-row-highlight' => $period['is_break']])>
                    <td class="text-right">
                        <div class="font-medium">{{ $period['name'] }}</div>
                        <div class="font-mono text-[11px] text-black/60 mt-1">
                            {{ date('H:i', strtotime($period['start_time'])) }} - {{ date('H:i', strtotime($period['end_time'])) }}
                        </div>
                    </td>

                    @foreach ($days as $day)
                        <td class="text-center align-middle">
                            @if ($period['is_break'])
                                <span class="text-black/50">{{ __('استراحة') }}</span>
                            @else
                                @php
                                    $item = $schedule['grid'][$period['id']][$day->value] ?? null;
                                @endphp

                                @if ($item)
                                    <div class="font-medium">{{ $item['subject']['name'] ?? '—' }}</div>
                                @else
                                    <span class="text-black/40">—</span>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
