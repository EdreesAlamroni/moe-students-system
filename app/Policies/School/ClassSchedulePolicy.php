<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;

class ClassSchedulePolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        return $user->can('class-schedule:view');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        return $user->can('class-schedule:update');
    }

    public function print(User $user, Classroom $classroom): bool
    {
        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        return $user->can('class-schedule:print');
    }
}
