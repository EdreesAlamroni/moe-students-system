<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum StudentLivingSituation: string
{
    use EnumUtilities;

    case WITH_PARENTS = 'with_parents';
    case WITH_FATHER = 'with_father';
    case WITH_MOTHER = 'with_mother';
    case WITH_RELATIVES = 'with_relatives';
    case FOSTER_FAMILY = 'foster_family';
    case OTHER = 'other';

    protected function getTranslationKey(): string
    {
        return 'student_living_situations';
    }
}
