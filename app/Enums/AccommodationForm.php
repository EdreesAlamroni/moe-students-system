<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum AccommodationForm: string
{
    use EnumUtilities;

    case FLAT = 'flat';
    case REGULAR_HOUSE = 'regular_house';
    case VILLA = 'villa';
    case WITH_RELATIVES = 'with_relatives';
    case OTHER = 'other';

    protected function getTranslationKey(): string
    {
        return 'accommodation_forms';
    }
}
