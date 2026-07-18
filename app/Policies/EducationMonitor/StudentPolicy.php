<?php

namespace App\Policies\EducationMonitor;

use App\Models\EducationMonitor;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student:view-any');
    }

    public function view(User $user, Student $student): bool
    {
        if (! $this->belongsToCurrentMonitor($user, $student)) {
            return false;
        }

        return $user->can('student:view');
    }

    private function belongsToCurrentMonitor(User $user, Student $student): bool
    {
        return $user->organization_type === EducationMonitor::class
            && $user->organization_id === $student->education_monitor_id;
    }
}
