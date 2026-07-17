<?php

namespace App\Policies\Warehouse;

use App\Models\EducationMonitor;
use App\Models\User;
use App\Models\Warehouse;

class EducationMonitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('education-monitor:view-any');
    }

    public function view(User $user, EducationMonitor $monitor): bool
    {
        if (! $this->belongsToCurrentWarehouse($user, $monitor)) {
            return false;
        }

        return $user->can('education-monitor:view');
    }

    private function belongsToCurrentWarehouse(User $user, EducationMonitor $monitor): bool
    {
        return $user->organization_type === Warehouse::class
            && $user->organization_id === $monitor->warehouse_id;
    }
}
