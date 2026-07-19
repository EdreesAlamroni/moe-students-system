<?php

namespace App\Policies\EducationServicesOffice;

use App\Models\User;

class StudentCountByGradeLevelReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:student-count-by-grade-level:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:student-count-by-grade-level:print');
    }
}
