<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum StudentExamEnrollmentStatus: string
{
    use EnumUtilities;

    case REGISTERED = 'registered';
    case DEFERRED = 'deferred';

    protected function getTranslationKey(): string
    {
        return 'student_exam_enrollment_statuses';
    }
}
