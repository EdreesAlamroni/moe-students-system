<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum FamilyIncome: string
{
    use EnumUtilities;

    case WEAK = 'weak';
    case AVERAGE = 'average';
    case ABOVE_AVERAGE = 'above_average';

    protected function getTranslationKey(): string
    {
        return 'family_incomes';
    }
}
