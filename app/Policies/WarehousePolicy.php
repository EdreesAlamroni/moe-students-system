<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('warehouse:view-any');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->can('warehouse:view');
    }

    public function create(User $user): bool
    {
        return $user->can('warehouse:create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        if ($warehouse->trashed()) {
            return false;
        }

        return $user->can('warehouse:update');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        if ($warehouse->hasAnyRelations()) {
            return false;
        }

        if ($warehouse->trashed()) {
            return false;
        }

        return $user->can('warehouse:delete');
    }
}
