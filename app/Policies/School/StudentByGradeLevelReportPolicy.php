<?php

namespace App\Policies\School;

use App\Models\User;

class StudentByGradeLevelReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:student-by-grade-level:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:student-by-grade-level:print');
    }
}
