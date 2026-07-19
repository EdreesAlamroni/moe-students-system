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

    private function belongsToCurrentOffice(User $user, Student $student): bool
    {
        if ($user->organization_type !== EducationServicesOffice::class) {
            return false;
        }

        if ($student->relationLoaded('school')) {
            return $user->organization_id === $student->school?->education_services_office_id;
        }

        return $student->school()
            ->where('education_services_office_id', '=', $user->organization_id)
            ->exists();
    }
}
