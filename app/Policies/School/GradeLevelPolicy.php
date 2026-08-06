<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\StudentEnrollment;
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

    public function create(User $user): bool
    {
        return $user->can('grade-level:create');
    }

    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        if (! $this->belongsToCurrentSchool($user, $gradeLevel)) {
            return false;
        }

        if ($gradeLevel->trashed()) {
            return false;
        }

        return $user->can('grade-level:update');
    }

    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        if (! $this->belongsToCurrentSchool($user, $gradeLevel)) {
            return false;
        }

        if ($this->hasEnrolledStudents($user, $gradeLevel)) {
            return false;
        }

        if ($gradeLevel->trashed()) {
            return false;
        }

        return $user->can('grade-level:delete');
    }

    public function transfer(User $user): bool
    {
        $schoolPeriod = $user->organization?->siblingPeriod();

        if (is_null($schoolPeriod)) {
            return false;
        }

        return $user->can('grade-level:transfer');
    }

    private function belongsToCurrentSchool(User $user, GradeLevel $gradeLevel): bool
    {
        if ($user->organization_type !== SchoolPeriod::class || is_null($user->organization_id)) {
            return false;
        }

        if (is_null(AcademicYear::currentId())) {
            return false;
        }

        if ($gradeLevel->relationLoaded('schoolPeriods')) {
            return $gradeLevel->schoolPeriods->contains('id', '=', $user->organization_id);
        }

        return $gradeLevel->schoolPeriods()
            ->where('school_periods.id', '=', $user->organization_id)
            ->exists();
    }

    private function hasEnrolledStudents(User $user, GradeLevel $gradeLevel): bool
    {
        return StudentEnrollment::query()
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->where('school_period_id', '=', $user->organization_id)
            ->where('grade_level_id', '=', $gradeLevel->id)
            ->exists();
    }
}
