<?php

namespace App\Policies\Administration;

use App\Models\EducationMonitor;
use App\Models\User;

class EducationMonitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('education-monitor:view-any');
    }

    public function view(User $user, EducationMonitor $monitor): bool
    {
        return $user->can('education-monitor:view');
    }

    public function create(User $user): bool
    {
        return $user->can('education-monitor:create');
    }

    public function update(User $user, EducationMonitor $monitor): bool
    {
        if ($monitor->trashed()) {
            return false;
        }

        return $user->can('education-monitor:update');
    }

    public function delete(User $user, EducationMonitor $monitor): bool
    {
        if ($monitor->hasAnyRelations()) {
            return false;
        }

        if ($monitor->trashed()) {
            return false;
        }

        return $user->can('education-monitor:delete');
    }
}
