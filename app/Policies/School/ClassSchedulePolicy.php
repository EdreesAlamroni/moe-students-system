<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;

class ClassSchedulePolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        if (! $this->belongsToCurrentSchool($user, $classroom)) {
            return false;
        }

        return $user->can('class-schedule:view');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if (! $this->belongsToCurrentSchool($user, $classroom)) {
            return false;
        }

        return $user->can('class-schedule:update');
    }

    public function print(User $user, Classroom $classroom): bool
    {
        if (! $this->belongsToCurrentSchool($user, $classroom)) {
            return false;
        }

        return $user->can('class-schedule:print');
    }

    private function belongsToCurrentSchool(User $user, Classroom $classroom): bool
    {
        return $user->organization_type === School::class && $user->organization_id === $classroom->school_id;
    }
}
