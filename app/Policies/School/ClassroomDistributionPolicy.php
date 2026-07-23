<?php

namespace App\Policies\School;

use App\Models\ClassroomDistributionCompletion;
use App\Models\User;

class ClassroomDistributionPolicy
{
    public function view(User $user): bool
    {
        return $user->can('classroom-distribution:view');
    }

    public function distribute(User $user): bool
    {
        return $user->can('classroom-distribution:distribute');
    }

    public function finalize(User $user): bool
    {
        $isDistributionCompleted = ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear();

        return $user->can('classroom-distribution:finalize') && ! $isDistributionCompleted;
    }
}
