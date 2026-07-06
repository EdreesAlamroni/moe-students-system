<?php

namespace App\Policies\Administration;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('academic-year:view-any');
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic-year:view');
    }

    public function create(User $user): bool
    {
        if (! is_null(AcademicYear::current())) {
            return false;
        }

        return $user->can('academic-year:create');
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        if ($academicYear->isClosed()) {
            return false;
        }

        if ($academicYear->trashed()) {
            return false;
        }

        return $user->can('academic-year:update');
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        if ($academicYear->isClosed()) {
            return false;
        }

        if ($academicYear->trashed()) {
            return false;
        }

        if ($academicYear->hasAnyRelations()) {
            return false;
        }

        return $user->can('academic-year:delete');
    }

    public function close(User $user, AcademicYear $academicYear): bool
    {
        if ($academicYear->isClosed()) {
            return false;
        }

        if ($academicYear->trashed()) {
            return false;
        }

        return $user->can('academic-year:close');
    }
}
