<?php

namespace App\Policies\Administration;

use App\Models\ClassPeriod;
use App\Models\User;

class ClassPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('class-period:view-any');
    }

    public function view(User $user, ClassPeriod $classPeriod): bool
    {
        return $user->can('class-period:view');
    }

    public function create(User $user): bool
    {
        return $user->can('class-period:create');
    }

    public function update(User $user, ClassPeriod $classPeriod): bool
    {
        if ($classPeriod->trashed()) {
            return false;
        }

        return $user->can('class-period:update');
    }

    public function delete(User $user, ClassPeriod $classPeriod): bool
    {
        if ($classPeriod->hasAnyRelations()) {
            return false;
        }

        if ($classPeriod->trashed()) {
            return false;
        }

        return $user->can('class-period:delete');
    }
}
