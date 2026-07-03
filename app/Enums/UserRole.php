<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum UserRole: string
{
    use EnumUtilities;

    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';

    protected function getTranslationKey(): string
    {
        return 'user_roles';
    }

    public function isManager(): bool
    {
        return $this === self::MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this === self::EMPLOYEE;
    }
}
