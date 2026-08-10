<x-print-layout :landscape="true" :title="__('تقرير الغياب')" :printed-by="auth('school')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير الغياب للطلبة')"
            :colspan="count($days) + 3"
            :organization-lines="auth('school')->user()->organization->printOrganizationLines()"
        >
            <x-slot:headerRight>
                <span>{{ __('السنة الدراسية') }}:</span>
                <span class="font-mono">{{ $academicYearName }}</span>
            </x-slot:headerRight>

            <x-slot:meta>
                <div class="print-meta-bar-item w-1/4 shrink-0">
                    <span>{{ __('الفصل الدراسي') }}:</span>
                    <span class="font-medium">{{ $classroom->gradeLevel->name }} / {{ $classroom->name }}</span>
                </div>

                <div class="print-meta-bar-item w-1/4 shrink-0">
                    <span>{{ __('الشهر') }}:</span>
                    <span class="font-medium">{{ $monthLabel }}</span>
                    <span class="font-mono">{{ $year }}</span>
                </div>
            </x-slot:meta>

            <x-slot:columns>
                <th scope="col" class="w-[2%] text-center">{{ __('ر.م') }}</th>
                <th scope="col" class="text-right">{{ __('اسم الطالب') }}</th>
                @foreach ($days as $day)
                    <th scope="col" class="w-[2%] px-1 text-center font-mono">{{ $day }}</th>
                @endforeach
                <th scope="col" class="w-[10%] text-center">{{ __('مجموع الغياب') }}</th>
            </x-slot:columns>

            @forelse ($students as $student)
                <tr>
                    <td class="text-center font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $student->full_name }}</td>
                    @foreach ($days as $day)
                        <td class="px-1 text-center"></td>
                    @endforeach
                    <td class="text-center"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($days) + 3 }}" class="print-empty-cell">
                        <x-empty-state class="justify-center" />
                    </td>
                </tr>
            @endforelse
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
