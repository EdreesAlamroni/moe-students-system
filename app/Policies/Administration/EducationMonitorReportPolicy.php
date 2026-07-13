<?php

namespace App\Policies\Administration;

use App\Models\User;

class EducationMonitorReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:education-monitor:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:education-monitor:print');
    }
}
