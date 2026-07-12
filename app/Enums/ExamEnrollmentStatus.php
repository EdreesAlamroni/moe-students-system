<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum ExamEnrollmentStatus: string
{
    use EnumUtilities;

    case REGISTERED = 'registered';
    case DEFERRED = 'deferred';

    protected function getTranslationKey(): string
    {
        return 'exam_enrollment_statuses';
    }
}
