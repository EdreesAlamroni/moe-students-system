<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum DayOfWeek: int
{
    use EnumUtilities;

    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    protected function getTranslationKey(): string
    {
        return 'days_of_week';
    }

    public static function schoolDays(): Collection
    {
        return collect([
            self::SUNDAY,
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
        ]);
    }

    public static function buildDays(): Collection
    {
        return self::schoolDays()->map(function (self $day): array {
            return [
                'id' => $day->value,
                'name' => $day->label(),
            ];
        })->values();
    }
}
