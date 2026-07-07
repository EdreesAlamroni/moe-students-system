<?php

namespace App\Policies\Administration;

use App\Models\GradeLevel;
use App\Models\User;

class GradeLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('grade-level:view-any');
    }

    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('grade-level:view');
    }

    public function create(User $user): bool
    {
        return $user->can('grade-level:create');
    }

    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        if ($gradeLevel->trashed()) {
            return false;
        }

        return $user->can('grade-level:update');
    }

    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        if ($gradeLevel->hasAnyRelations()) {
            return false;
        }

        if ($gradeLevel->trashed()) {
            return false;
        }

        return $user->can('grade-level:delete');
    }
}
