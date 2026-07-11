<?php

namespace App\Policies\Administration;

use App\Models\EducationServicesOffice;
use App\Models\User;

class EducationServicesOfficePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('education-services-office:view-any');
    }

    public function view(User $user, EducationServicesOffice $office): bool
    {
        return $user->can('education-services-office:view');
    }

    public function create(User $user): bool
    {
        return $user->can('education-services-office:create');
    }

    public function update(User $user, EducationServicesOffice $office): bool
    {
        if ($office->trashed()) {
            return false;
        }

        return $user->can('education-services-office:update');
    }

    public function delete(User $user, EducationServicesOffice $office): bool
    {
        if ($office->hasAnyRelations()) {
            return false;
        }

        if ($office->trashed()) {
            return false;
        }

        return $user->can('education-services-office:delete');
    }
}
