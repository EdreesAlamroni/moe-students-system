<?php

namespace App\Policies\Administration;

use App\Models\School;
use App\Models\User;

class SchoolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('school:view-any');
    }

    public function view(User $user, School $school): bool
    {
        return $user->can('school:view');
    }

    public function create(User $user): bool
    {
        return $user->can('school:create');
    }

    public function update(User $user, School $school): bool
    {
        if ($school->trashed()) {
            return false;
        }

        return $user->can('school:update');
    }

    public function delete(User $user, School $school): bool
    {
        if ($school->hasAnyRelations()) {
            return false;
        }

        if ($school->trashed()) {
            return false;
        }

        return $user->can('school:delete');
    }
}
