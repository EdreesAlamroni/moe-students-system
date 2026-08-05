<?php

namespace App\Policies\EducationMonitor;

use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user:view-any');
    }

    public function view(User $user, User $target): bool
    {
        if (! $this->sharesMonitorWith($user, $target)) {
            return false;
        }

        return $user->can('user:view');
    }

    public function create(User $user, ?UserScope $scope = null): bool
    {
        if (! is_null($scope) && $user->scope->getAccessibleScopes()->doesntContain($scope)) {
            return false;
        }

        if (! $this->hasEducationMonitorContext($user)) {
            return false;
        }

        return $user->can('user:create');
    }

    public function update(User $user, User $target): bool
    {
        if (! $this->sharesMonitorWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:update');
    }

    public function delete(User $user, User $target): bool
    {
        if (! $this->sharesMonitorWith($user, $target)) {
            return false;
        }

        if ($user->is($target)) {
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
        if (! $this->sharesMonitorWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:state-update');
    }

    private function sharesMonitorWith(User $user, User $target): bool
    {
        if ($target->isEducationMonitorStaff()) {
            return $user->organization_id === $target->organization_id;
        }

        if (is_null($target->organization_id)) {
            return false;
        }

        $targetMonitorId = match (true) {
            $target->relationLoaded('organization') => $target->organization?->education_monitor_id,
            $target->isEducationServicesOfficeStaff() => EducationServicesOffice::query()->where('id', '=', $target->organization_id)->value('education_monitor_id'),
            $target->isSchoolStaff() => SchoolPeriod::query()->where('id', '=', $target->organization_id)->value('education_monitor_id'),
            default => null,
        };

        if (is_null($targetMonitorId)) {
            return false;
        }

        return $user->organization_id === $targetMonitorId;
    }

    private function hasEducationMonitorContext(User $user): bool
    {
        if ($user->organization_type !== EducationMonitor::class || is_null($user->organization_id)) {
            return false;
        }

        return $user->organization()->exists();
    }
}
