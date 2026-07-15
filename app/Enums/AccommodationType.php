<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum AccommodationType: string
{
    use EnumUtilities;

    case OWNED = 'owned';
    case RENTAL = 'rental';
    case DISPLACED = 'displaced';
    case OTHER = 'other';

    protected function getTranslationKey(): string
    {
        return 'accommodation_types';
    }
}
