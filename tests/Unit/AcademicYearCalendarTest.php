<?php

use App\Support\AcademicYearCalendar;
use Illuminate\Support\Carbon;

test('start year from date uses september as the academic year boundary', function () {
    expect(AcademicYearCalendar::startYearFromDate(Carbon::parse('2026-08-31')))->toBe(2025)
        ->and(AcademicYearCalendar::startYearFromDate(Carbon::parse('2026-09-01')))->toBe(2026);
});

test('name for start year uses the yyyy yyyy convention', function () {
    expect(AcademicYearCalendar::nameForStartYear(2025))->toBe('2026/2025')
        ->and(AcademicYearCalendar::nameForStartYear(2026))->toBe('2027/2026');
});

test('historical definitions mark only the active start year', function () {
    $definitions = AcademicYearCalendar::historicalDefinitions(2, 2025);

    expect($definitions)->toHaveCount(3)
        ->and($definitions[0]['name'])->toBe('2024/2023')
        ->and($definitions[0]['is_active'])->toBeFalse()
        ->and($definitions[2]['name'])->toBe('2026/2025')
        ->and($definitions[2]['is_active'])->toBeTrue();
});

test('attributes for start year include name dates and active flag', function () {
    expect(AcademicYearCalendar::attributesForStartYear(2025, true))->toBe([
        'name' => '2026/2025',
        'start_date' => '2025-09-01',
        'end_date' => '2026-06-30',
        'is_active' => true,
    ]);
});
