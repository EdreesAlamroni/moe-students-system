<x-print-layout :title="__('إحصائية الطلاب حسب الصفوف الدراسية')" :printed-by="auth('education_monitor')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('إحصائية الطلاب حسب الصفوف الدراسية')"
            :colspan="4"
            :organization-lines="auth('education_monitor')->user()->organization->printOrganizationLines()"
        >
            <x-slot:headerRight>
                <span>{{ __('السنة الدراسية') }}:</span>
                <span class="font-mono">{{ $academicYearName }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('اسم الصف الدراسي') }}</th>
                <th scope="col">{{ __('المرحلة الدراسية') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($gradeLevels as $gradeLevel)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $gradeLevel->name }}</td>
                    <td>{{ $gradeLevel->educational_stage->label() }}</td>
                    <td class="text-center font-mono">{{ $gradeLevel->students_count ?? 0 }}</td>
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
