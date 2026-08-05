<?php

namespace App\Policies\EducationServicesOffice;

use App\Models\EducationServicesOffice;
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
        if (! $this->belongsToCurrentOffice($user, $student)) {
            return false;
        }

        return $user->can('student:view');
    }

    public function viewAcademicRecord(User $user, Student $student): bool
    {
        if (! $this->belongsToCurrentOffice($user, $student)) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:view-academic-record');
    }

    public function viewPsychosocialCard(User $user, Student $student): bool
    {
        if (! $this->belongsToCurrentOffice($user, $student)) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:view-psychosocial-card');
    }

    private function belongsToCurrentOffice(User $user, Student $student): bool
    {
        if ($user->organization_type !== EducationServicesOffice::class) {
            return false;
        }

        if ($student->relationLoaded('schoolPeriod')) {
            return $user->organization_id === $student->schoolPeriod?->education_services_office_id;
        }

        return $student->schoolPeriod()
            ->where('education_services_office_id', '=', $user->organization_id)
            ->exists();
    }
}
