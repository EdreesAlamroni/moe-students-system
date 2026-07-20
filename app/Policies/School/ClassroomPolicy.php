<?php

namespace App\Policies\School;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('classroom:view-any');
    }

    public function view(User $user, Classroom $classroom): bool
    {
        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        return $user->can('classroom:view');
    }

    public function create(User $user): bool
    {
        return $user->can('classroom:create');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        if ($classroom->trashed()) {
            return false;
        }

        return $user->can('classroom:update');
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        if ($classroom->hasAnyRelations()) {
            return false;
        }

        if ($classroom->school_id !== $user->organization_id) {
            return false;
        }

        if ($classroom->trashed()) {
            return false;
        }

        return $user->can('classroom:delete');
    }
}
