<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum DayOfWeek: int
{
    use EnumUtilities;

    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    protected function getTranslationKey(): string
    {
        return 'days_of_week';
    }

    public static function schoolDays(): Collection
    {
        return collect([
            self::Sunday,
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
        ]);
    }

    public static function buildDays(): Collection
    {
        return self::schoolDays()->map(function (self $day) {
            return [
                'id' => $day->value,
                'name' => $day->label(),
            ];
        })->values();
    }
}
