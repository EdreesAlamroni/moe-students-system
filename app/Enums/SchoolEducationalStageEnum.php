<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum SchoolEducationalStageEnum: string
{
    use EnumUtilities;

    case KINDERGARTEN = 'kindergarten';
    case PRIMARY_EDUCATION = 'primary_education';
    case SECONDARY_EDUCATION = 'secondary_education';

    protected function getTranslationKey(): string
    {
        return 'school_educational_stages';
    }
}
