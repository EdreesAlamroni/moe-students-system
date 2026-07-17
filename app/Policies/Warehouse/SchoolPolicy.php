<?php

namespace App\Policies\Warehouse;

use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;

class SchoolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('school:view-any');
    }

    public function view(User $user, School $school): bool
    {
        if (! $this->belongsToCurrentWarehouse($user, $school)) {
            return false;
        }

        return $user->can('school:view');
    }

    private function belongsToCurrentWarehouse(User $user, School $school): bool
    {
        $school->loadMissing('monitor:id,warehouse_id');

        return $user->organization_type === Warehouse::class
            && $user->organization_id === $school->monitor->warehouse_id;
    }
}
