<x-print-layout :title="__('تقرير الطلاب حسب الصفوف الدراسية')" :printed-by="auth('school')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير الطلاب حسب الصفوف الدراسية')"
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
                <th scope="col">{{ __('الصف الدراسي') }}</th>
            </x-slot:columns>

            @forelse ($students as $student)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>
                        @if ($student->is_libyan)
                            <span class="font-mono">{{ $student->national_id }}</span>
                        @else
                            {{ $student->nationality?->full_name ?? __('أجنبي') }}
                        @endif
                    </td>
                    <td>{{ $student->enrollment?->gradeLevel?->name }}</td>
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
