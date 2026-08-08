<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class AcademicYearCalendar
{
    public const START_MONTH = 9;

    public const END_MONTH = 6;

    public const END_DAY = 30;

    public static function startYearFromDate(CarbonInterface $date): int
    {
        $date = Carbon::instance($date)->startOfDay();

        return $date->month >= self::START_MONTH
            ? $date->year
            : $date->year - 1;
    }

    public static function nameForStartYear(int $startYear): string
    {
        return sprintf('%d/%d', $startYear + 1, $startYear);
    }

    public static function attributesForStartYear(int $startYear, bool $isActive = false): array
    {
        return [
            'name' => self::nameForStartYear($startYear),
            'start_date' => Carbon::create($startYear, self::START_MONTH, 1)->toDateString(),
            'end_date' => Carbon::create($startYear + 1, self::END_MONTH, self::END_DAY)->toDateString(),
            'is_active' => $isActive,
        ];
    }

    public static function historicalDefinitions(int $yearsBack, int $activeStartYear): array
    {
        $years = [];

        for ($offset = $yearsBack; $offset >= 0; $offset--) {
            $startYear = $activeStartYear - $offset;

            $years[] = self::attributesForStartYear($startYear, $offset === 0);
        }

        return $years;
    }
}
