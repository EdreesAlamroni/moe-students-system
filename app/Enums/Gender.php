<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum Gender: string
{
    use EnumUtilities;

    case MALE = 'male';
    case FEMALE = 'female';

    protected function getTranslationKey(): string
    {
        return 'gender';
    }
}
