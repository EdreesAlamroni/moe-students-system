<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum AcademicRecordStatus: string
{
    use EnumUtilities;

    case PASSED = 'passed';
    case PROMOTED = 'promoted';
    case FAILED = 'failed';

    protected function getTranslationKey(): string
    {
        return 'academic_record_statuses';
    }

    public function isPassing(): bool
    {
        return match ($this) {
            self::PASSED, self::PROMOTED => true,
            self::FAILED => false,
        };
    }

    public static function selectable(): Collection
    {
        return collect([self::PASSED, self::FAILED])->map(function (self $case): array {
            return [
                'id' => $case->value,
                'name' => $case->label(),
            ];
        });
    }
}
