<?php

namespace App\Policies\School;

use App\Models\User;

class StudentByClassroomReportPolicy
{
    public function view(User $user): bool
    {
        return $user->can('report:student-by-classroom:view');
    }

    public function print(User $user): bool
    {
        return $user->can('report:student-by-classroom:print');
    }
}
