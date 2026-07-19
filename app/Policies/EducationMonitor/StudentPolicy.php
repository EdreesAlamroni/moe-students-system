<?php

namespace App\Policies\EducationMonitor;

use App\Models\AcademicYear;
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

    public function addTransferredStudent(User $user): bool
    {
        return $user->can('student:add-transferred-student');
    }

    public function transferStudentOut(User $user, Student $student): bool
    {
        // TODO: Check if can transfer a student if academic year is inactive.
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if (! $this->belongsToCurrentMonitor($user, $student)) {
            return false;
        }

        if (! is_null($student->school_id)) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:transfer-student-out-of-monitor');
    }

    private function belongsToCurrentMonitor(User $user, Student $student): bool
    {
        return $user->organization_type === EducationMonitor::class
            && $user->organization_id === $student->education_monitor_id;
    }
}
