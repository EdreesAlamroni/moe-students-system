<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum SchoolBranchType: string
{
    use EnumUtilities;

    case MAIN = 'main';
    case SUB = 'sub';

    protected function getTranslationKey(): string
    {
        return 'school_branch_types';
    }
}
