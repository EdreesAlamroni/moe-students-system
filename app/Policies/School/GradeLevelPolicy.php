<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
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

    public function transfer(User $user, GradeLevel $gradeLevel): bool
    {
        if (! $this->belongsToCurrentSchool($user, $gradeLevel)) {
            return false;
        }

        if ($gradeLevel->trashed()) {
            return false;
        }

        return $user->can('grade-level:transfer');
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

    private function hasEnrolledStudents(User $user, GradeLevel $gradeLevel): bool
    {
        return StudentEnrollment::query()
            ->where('grade_level_id', '=', $gradeLevel->id)
            ->where('school_id', '=', $user->organization_id)
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->exists();
    }
}
