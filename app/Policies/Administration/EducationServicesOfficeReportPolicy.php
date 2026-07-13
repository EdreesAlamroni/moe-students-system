<?php

namespace App\Policies\Administration;

use App\Models\User;

class EducationServicesOfficeReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:education-services-office:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:education-services-office:print');
    }
}
