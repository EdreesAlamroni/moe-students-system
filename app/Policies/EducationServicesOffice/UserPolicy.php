<?php

namespace App\Policies\EducationServicesOffice;

use App\Enums\UserScope;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user:view-any');
    }

    public function view(User $user, User $target): bool
    {
        if (! $this->sharesOfficeWith($user, $target)) {
            return false;
        }

        return $user->can('user:view');
    }

    public function create(User $user, ?UserScope $scope = null): bool
    {
        if (! is_null($scope) && $user->scope->getAccessibleScopes()->doesntContain($scope)) {
            return false;
        }

        return $user->can('user:create');
    }

    public function update(User $user, User $target): bool
    {
        if (! $this->sharesOfficeWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:update');
    }

    public function delete(User $user, User $target): bool
    {
        if (! $this->sharesOfficeWith($user, $target)) {
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
        if (! $this->sharesOfficeWith($user, $target)) {
            return false;
        }

        if ($target->trashed()) {
            return false;
        }

        return $user->can('user:state-update');
    }

    private function sharesOfficeWith(User $user, User $target): bool
    {
        if ($target->isEducationServicesOfficeStaff()) {
            return $user->organization_id === $target->organization_id;
        }

        $targetOfficeId = $target->organization?->education_services_office_id;

        if (is_null($targetOfficeId)) {
            return false;
        }

        return $user->organization_id === $targetOfficeId;
    }
}
