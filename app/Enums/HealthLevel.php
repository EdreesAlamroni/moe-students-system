<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum HealthLevel: string
{
    use EnumUtilities;

    case WEAK = 'weak';
    case NORMAL = 'normal';
    case GOOD = 'good';
    case EXCELLENT = 'excellent';

    protected function getTranslationKey(): string
    {
        return 'health_levels';
    }
}
