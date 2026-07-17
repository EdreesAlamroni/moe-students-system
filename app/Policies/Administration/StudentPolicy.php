<?php

namespace App\Policies\Administration;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student:view-any');
    }

    public function view(User $user, Student $student): bool
    {
        return $user->can('student:view');
    }

    public function create(User $user): bool
    {
        return $user->can('student:create');
    }

    public function update(User $user, Student $student): bool
    {
        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:update');
    }

    public function delete(User $user, Student $student): bool
    {
        if ($student->hasAnyRelations()) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:delete');
    }
}
