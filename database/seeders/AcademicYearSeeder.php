<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Support\AcademicYearCalendar;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    private const REFERENCE_ACTIVE_START_YEAR = 2026;

    private const HISTORY_YEARS = 30;

    public function run(): void
    {
        $historyYears = AcademicYearCalendar::historicalDefinitions(self::HISTORY_YEARS, self::REFERENCE_ACTIVE_START_YEAR);

        foreach ($historyYears as $attributes) {
            AcademicYear::query()->firstOrCreate(['name' => $attributes['name']], $attributes);
        }

        $existsActiveYear = AcademicYear::query()->active()->exists();

        if (! $existsActiveYear) {
            $activeYear = AcademicYear::query()
                ->where('name', '=', AcademicYearCalendar::nameForStartYear(self::REFERENCE_ACTIVE_START_YEAR))
                ->firstOrFail();

            AcademicYear::activate($activeYear);
        }
    }
}
