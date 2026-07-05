<?php

namespace App\Policies\Administration;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the actor and target belong to the same organizational context.
     */
    // protected function sharesOrganizationWith(User $user, User $target): bool
    // {
    //     if ($target->isSchoolStaff()) {
    //         return $user->model_id === $target->model_id;
    //     }

    //     $schoolId = $target->model?->school_id;

    //     return $schoolId !== null && $user->model_id === $schoolId;
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('user:view-any');
    }

    public function view(User $user, User $target): bool
    {
        if ($target->isAdministrator()) {
            return false;
        }

        return $user->can('user:view');
    }

    public function create(User $user): bool
    {
        return $user->can('user:create');
    }

    public function update(User $user, User $target): bool
    {
        if ($target->isAdministrator()) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:update');
    }

    public function delete(User $user, User $target): bool
    {
        if ($target->hasAnyRelations()) {
            return false;
        }

        if ($target->isAdministrator()) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:delete');
    }

    public function stateUpdate(User $user, User $target): bool
    {
        if ($target->isAdministrator()) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:state-update');
    }
}
