<x-print.report-header
    :title="$title"
    :organization-lines="$school->printOrganizationLines()"
>
    <x-slot:right>
        <span>{{ __('السنة الدراسية') }}:</span>
        <span class="font-mono">{{ $academicYearName }}</span>
    </x-slot:right>
</x-print.report-header>
