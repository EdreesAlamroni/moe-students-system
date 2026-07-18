<?php

namespace App\Policies\EducationMonitor;

use App\Models\EducationMonitor;
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
        if (! $this->belongsToCurrentMonitor($user, $school)) {
            return false;
        }

        return $user->can('school:view');
    }

    public function create(User $user): bool
    {
        return $user->can('school:create');
    }

    public function update(User $user, School $school): bool
    {
        if (! $this->belongsToCurrentMonitor($user, $school)) {
            return false;
        }

        if ($school->trashed()) {
            return false;
        }

        return $user->can('school:update');
    }

    public function delete(User $user, School $school): bool
    {
        if (! $this->belongsToCurrentMonitor($user, $school)) {
            return false;
        }

        if ($school->hasAnyRelations()) {
            return false;
        }

        if ($school->trashed()) {
            return false;
        }

        return $user->can('school:delete');
    }

    private function belongsToCurrentMonitor(User $user, School $school): bool
    {
        return $user->organization_type === EducationMonitor::class
            && $user->organization_id === $school->education_monitor_id;
    }
}
