@props([
    'title',
    'colspan',
    'organizationName' => null,
    'organizationLines' => null,
])

{{--
    Single-table report. The <thead> (institutional masthead, optional meta bar
    and the column header row) is repeated by the browser on every printed page,
    while <tbody> rows flow and paginate naturally.

    Slots:
        - headerRight: key/value content shown in the header's right meta box
        - meta:        optional contextual meta bar rendered above the columns
        - columns:     the <th> cells of the column header row
        - default:     the <tbody> rows (and/or empty state)
--}}
<div class="print-surface">
    <table {{ $attributes->class(['print-report print-report-compact']) }}>
        <thead>
            <tr class="print-masthead">
                <th colspan="{{ $colspan }}" scope="colgroup">
                    <x-print.report-header
                        :title="$title"
                        :organization-name="$organizationName"
                        :organization-lines="$organizationLines"
                    >
                        @isset($headerRight)
                            <x-slot:right>{{ $headerRight }}</x-slot:right>
                        @endisset
                    </x-print.report-header>
                </th>
            </tr>

            @isset($meta)
                <tr class="print-meta">
                    <th colspan="{{ $colspan }}" scope="colgroup">
                        <div class="print-meta-bar">{{ $meta }}</div>
                    </th>
                </tr>
            @endisset

            <tr class="print-columns">
                {{ $columns }}
            </tr>
        </thead>

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
