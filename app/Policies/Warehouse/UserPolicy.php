<?php

namespace App\Policies\Warehouse;

use App\Models\User;
use App\Models\Warehouse;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user:view-any');
    }

    public function view(User $user, User $target): bool
    {
        if (! $this->sharesOrganizationWith($user, $target)) {
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
        if (! $this->sharesOrganizationWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:update');
    }

    public function delete(User $user, User $target): bool
    {
        if (! $this->sharesOrganizationWith($user, $target)) {
            return false;
        }

        if ($target->hasAnyRelations()) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:delete');
    }

    public function stateUpdate(User $user, User $target): bool
    {
        if (! $this->sharesOrganizationWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:state-update');
    }

    private function sharesOrganizationWith(User $user, User $target): bool
    {
        return $target->organization_type === Warehouse::class
            && $user->organization_type === Warehouse::class
            && $user->organization_id === $target->organization_id;
    }
}
