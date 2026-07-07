<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum GradeLevelEnum: string
{
    use EnumUtilities;

    case KG_1 = 'kg_1';
    case KG_2 = 'kg_2';

    case GRADE_1 = 'grade_1';
    case GRADE_2 = 'grade_2';
    case GRADE_3 = 'grade_3';
    case GRADE_4 = 'grade_4';
    case GRADE_5 = 'grade_5';
    case GRADE_6 = 'grade_6';
    case GRADE_7 = 'grade_7';
    case GRADE_8 = 'grade_8';
    case GRADE_9 = 'grade_9';

    case GRADE_10 = 'grade_10';
    case GRADE_11_SCIENTIFIC = 'grade_11_scientific';
    case GRADE_11_LITERARY = 'grade_11_literary';
    case GRADE_12_SCIENTIFIC = 'grade_12_scientific';
    case GRADE_12_LITERARY = 'grade_12_literary';

    private const KINDERGARTEN_MAX_ORDER = 2;

    private const PRIMARY_EDUCATION_MAX_ORDER = 11;

    protected function getTranslationKey(): string
    {
        return 'grade_levels';
    }

    public function label(): string
    {
        $translationKey = sprintf('app.enums.%s.%s', $this->getTranslationKey(), $this->value);
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return Str::of($this->value)->replace('_', ' ')->title()->toString();
    }

    public function order(): int
    {
        return match ($this) {
            self::KG_1 => 1,
            self::KG_2 => 2,
            self::GRADE_1 => 3,
            self::GRADE_2 => 4,
            self::GRADE_3 => 5,
            self::GRADE_4 => 6,
            self::GRADE_5 => 7,
            self::GRADE_6 => 8,
            self::GRADE_7 => 9,
            self::GRADE_8 => 10,
            self::GRADE_9 => 11,
            self::GRADE_10 => 12,
            self::GRADE_11_SCIENTIFIC, self::GRADE_11_LITERARY => 13,
            self::GRADE_12_SCIENTIFIC, self::GRADE_12_LITERARY => 14,
        };
    }

    /**
     * Resolve the educational stage for this grade level.
     *
     * Boundaries align with {@see order()}:
     *  - Kindergarten: orders 1–2 (KG 1–2)
     *  - Primary education: orders 3–11 (Grades 1–9)
     *  - Secondary education: orders 12–14 (Grades 10–12)
     */
    public function stage(): SchoolEducationalStageEnum
    {
        $order = $this->order();

        if ($order <= self::KINDERGARTEN_MAX_ORDER) {
            return SchoolEducationalStageEnum::KINDERGARTEN;
        }

        if ($order <= self::PRIMARY_EDUCATION_MAX_ORDER) {
            return SchoolEducationalStageEnum::PRIMARY_EDUCATION;
        }

        return SchoolEducationalStageEnum::SECONDARY_EDUCATION;
    }

    public static function filteredByStage(SchoolEducationalStageEnum|string $stage): Collection
    {
        $stage = is_string($stage) ? SchoolEducationalStageEnum::from($stage) : $stage;

        return collect(self::cases())
            ->filter(function (self $case) use ($stage): bool {
                return $case->stage() === $stage;
            })
            ->sortBy(function (self $case): int {
                return $case->order();
            })
            ->values();
    }
}
