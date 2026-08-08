<?php

namespace App\Policies\Administration;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user:view-any');
    }

    public function view(User $user, User $target): bool
    {
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

    public function updatePassword(User $user, User $target): bool
    {
        if (! $user->isAdministrator()) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:update');
    }
}
