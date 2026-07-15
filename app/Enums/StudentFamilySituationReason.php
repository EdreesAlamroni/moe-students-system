<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum StudentFamilySituationReason: string
{
    use EnumUtilities;

    case PARENTS_SEPARATION = 'parents_separation';
    case MOTHER_DEATH = 'mother_death';
    case FATHER_DEATH = 'father_death';

    protected function getTranslationKey(): string
    {
        return 'student_family_situation_reasons';
    }
}
