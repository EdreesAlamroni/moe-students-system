<x-print-layout :title="__('تقرير الطلبة حسب الفصول الدراسية')" :printed-by="auth('school')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير الطلبة حسب الفصول الدراسية')"
            :colspan="4"
            :organization-lines="auth('school')->user()->organization->printOrganizationLines()"
        >
            <x-slot:headerRight>
                <span>{{ __('السنة الدراسية') }}:</span>
                <span class="font-mono">{{ $academicYearName }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('اسم الطالب') }}</th>
                <th scope="col">{{ __('الرقم الوطني / الجنسية') }}</th>
                <th scope="col">{{ __('الصف الدراسي / الفصل الدراسي') }}</th>
            </x-slot:columns>

            @forelse ($students as $student)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $student->fullName }}</td>
                    <td>
                        @if ($student->isLibyan)
                            <span class="font-mono">{{ $student->national_id }}</span>
                        @else
                            {{ $student->nationality?->fullName ?? __('أجنبي') }}
                        @endif
                    </td>
                    <td>
                        @if ($gradeLevel = $student->enrollment?->gradeLevel)
                            {{ $gradeLevel->name }}
                            @if ($classroom = $student->enrollment->classroom)
                                <span class="ms-0.5">/<span class="ms-1 font-mono">{{ $classroom->name }}</span></span>
                            @endif
                        @else
                            <span class="font-mono">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="print-empty-cell">
                        <x-empty-state class="justify-center" />
                    </td>
                </tr>
            @endforelse
        </x-print.report-table>
    </x-slot:content>
</x-print-layout>
