<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;

class GradeLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('grade-level:view-any');
    }

    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        if (! $this->belongsToCurrentSchool($user, $gradeLevel)) {
            return false;
        }

        return $user->can('grade-level:view');
    }

    private function belongsToCurrentSchool(User $user, GradeLevel $gradeLevel): bool
    {
        if ($user->organization_type !== School::class || $user->organization_id === null) {
            return false;
        }

        if (AcademicYear::currentId() === null) {
            return false;
        }

        if ($gradeLevel->relationLoaded('schools')) {
            return $gradeLevel->schools->contains(function (School $school) use ($user): bool {
                return $school->id === $user->organization_id;
            });
        }

        return $gradeLevel->schools()
            ->whereKey($user->organization_id)
            ->exists();
    }
}
