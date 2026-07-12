<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum SchoolBuildingType: string
{
    use EnumUtilities;

    case SCHOOL = 'school';
    case VILLA = 'villa';
    case FLAT = 'flat';
    case OTHERWISE = 'otherwise';

    protected function getTranslationKey(): string
    {
        return 'school_building_types';
    }
}
