<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum SchoolAcademicPeriod: string
{
    use EnumUtilities;

    case MORNING = 'morning';
    case EVENING = 'evening';
    case DUAL_PERIOD = 'dual_period';

    protected function getTranslationKey(): string
    {
        return 'school_academic_periods';
    }

    public static function getPrimaryPeriods(): Collection
    {
        static $periods;

        return $periods ??= collect([self::MORNING, self::EVENING])->map(function (self $case) {
            return [
                'id' => $case->value,
                'name' => $case->label(),
            ];
        })->values();
    }

    public static function getPrimaryValues(): array
    {
        return self::getPrimaryPeriods()->pluck('id')->all();
    }

    public function isMorning(): bool
    {
        return $this === self::MORNING;
    }

    public function isEvening(): bool
    {
        return $this === self::EVENING;
    }

    public function isDualPeriod(): bool
    {
        return $this === self::DUAL_PERIOD;
    }

    public function isSinglePeriod(): bool
    {
        return ! $this->isDualPeriod();
    }
}
