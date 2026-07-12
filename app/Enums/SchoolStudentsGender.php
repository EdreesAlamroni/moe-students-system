<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum SchoolStudentsGender: string
{
    use EnumUtilities;

    case BOYS = 'boys';
    case GIRLS = 'girls';
    case MIXED = 'mixed';

    protected function getTranslationKey(): string
    {
        return 'school_students_gender';
    }
}
