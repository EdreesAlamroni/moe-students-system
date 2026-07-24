<?php

namespace App\Policies\School;

use App\Models\User;

class AttendanceReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:attendance:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:attendance:print');
    }
}
