<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum AcademicRecordRating: string
{
    use EnumUtilities;

    case EXCELLENT = 'excellent';
    case VERY_GOOD = 'very_good';
    case GOOD = 'good';
    case SATISFACTORY = 'satisfactory';

    protected function getTranslationKey(): string
    {
        return 'academic_record_ratings';
    }
}
