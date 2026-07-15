<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum StudentRegistrationStatus: string
{
    use EnumUtilities;

    case NEW = 'new';
    case REPEATER = 'repeater';
    case EXCEPTIONAL_YEAR = 'exceptional_year';
    case COMPLEMENTARY = 'complementary';

    protected function getTranslationKey(): string
    {
        return 'student_registration_statuses';
    }
}
