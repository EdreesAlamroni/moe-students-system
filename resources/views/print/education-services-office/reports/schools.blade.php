<x-print-layout :title="__('تقرير المدارس')" :printed-by="auth('education_services_office')->user()->name">
    <x-slot:content>
        <x-print.report-table
            :title="__('تقرير المدارس')"
            :colspan="4"
            :organization-lines="auth('education_services_office')->user()->organization->printOrganizationLines()"
        >
            <x-slot:headerRight>
                <span>{{ __('السنة الدراسية') }}:</span>
                <span class="font-mono">{{ $academicYearName }}</span>
            </x-slot:headerRight>

            <x-slot:columns>
                <th scope="col">{{ __('ر.م') }}</th>
                <th scope="col">{{ __('اسم المدرسة') }}</th>
                <th scope="col">{{ __('نوع المدرسة') }}</th>
                <th scope="col" class="text-center">{{ __('عدد الطلاب') }}</th>
            </x-slot:columns>

            @forelse ($schools as $school)
                <tr>
                    <td class="font-mono">{{ $loop->iteration }}</td>
                    <td>{{ $school->name }}</td>
                    <td>{{ $school->type->label() }}</td>
                    <td class="text-center font-mono">{{ $school->students_count ?? 0 }}</td>
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
