<?php

namespace App\Policies\School;

use App\Models\AcademicYear;
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
        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        return $user->can('student:view');
    }

    public function create(User $user): bool
    {
        return $user->can('student:create');
    }

    public function update(User $user, Student $student): bool
    {
        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:update');
    }

    public function delete(User $user, Student $student): bool
    {
        if ($student->hasAnyRelations()) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:delete');
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

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:transfer-student-out-of-school');
    }

    public function enrollInGradeLevel(User $user, Student $student): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if ($student->hasEnrollment()) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:enroll-in-grade-level');
    }

    public function enrollInClassroom(User $user, Student $student): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        $student->loadMissing(['enrollment']);

        if (! $student->hasEnrollment() || filled($student->enrollment->classroom_id)) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:enroll-in-classroom');
    }

    public function transferClassroom(User $user, Student $student): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        $student->loadMissing(['enrollment']);

        if (! $student->hasEnrollment() || blank($student->enrollment->classroom_id)) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:transfer-classroom');
    }

    public function viewPsychosocialCard(User $user, Student $student): bool
    {
        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:view-psychosocial-card');
    }

    public function updatePsychosocialCard(User $user, Student $student): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if ($student->doesntHaveEnrollment()) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:update-psychosocial-card');
    }

    public function printPsychosocialCard(User $user, Student $student): bool
    {
        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:print-psychosocial-card');
    }

    public function viewAcademicRecord(User $user, Student $student): bool
    {
        if (AcademicYear::isCurrentYearInactive()) {
            return false;
        }

        if ($student->doesntHaveEnrollment()) {
            return false;
        }

        if ($student->school_id !== $user->organization_id) {
            return false;
        }

        if ($student->trashed()) {
            return false;
        }

        return $user->can('student:view-academic-record');
    }

    // TODO: Remove comments after implementing the academic record feature.
    // public function createAcademicRecord(User $user, Student $student): bool
    // {
    //     if (AcademicYear::isCurrentYearInactive()) {
    //         return false;
    //     }

    //     if ($student->doesntHaveEnrollment()) {
    //         return false;
    //     }

    //     if ($student->school_id !== $user->organization_id) {
    //         return false;
    //     }

    //     $academicRecordService = app(AcademicRecordService::class);

    //     if (! $academicRecordService->requiresAcademicRecord($student)) {
    //         return false;
    //     }

    //     if ($academicRecordService->isComplete($student)) {
    //         return false;
    //     }

    //     if ($student->trashed()) {
    //         return false;
    //     }

    //     return $user->can('student:create-academic-record');
    // }
}
