<?php

namespace App\Policies\Administration;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subject:view-any');
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->can('subject:view');
    }

    public function create(User $user): bool
    {
        return $user->can('subject:create');
    }

    public function update(User $user, Subject $subject): bool
    {
        if ($subject->trashed()) {
            return false;
        }

        return $user->can('subject:update');
    }

    public function delete(User $user, Subject $subject): bool
    {
        if ($subject->hasAnyRelations()) {
            return false;
        }

        if ($subject->trashed()) {
            return false;
        }

        return $user->can('subject:delete');
    }
}
