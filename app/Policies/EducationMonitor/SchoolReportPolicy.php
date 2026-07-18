<?php

namespace App\Policies\EducationMonitor;

use App\Models\User;

class SchoolReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:school:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:school:print');
    }
}
