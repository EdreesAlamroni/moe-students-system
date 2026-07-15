<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;

enum StudentBehavioralProblem: string
{
    use EnumUtilities;

    case SHYNESS = 'shyness';
    case INTROVERSION_ISOLATION = 'introversion_isolation';
    case FEAR = 'fear';
    case LACK_OF_SELF_CONFIDENCE = 'lack_of_self_confidence';
    case LYING = 'lying';
    case SLEEP_DISORDERS = 'sleep_disorders';
    case ATTENTION_DEFICIT = 'attention_deficit';
    case THUMB_SUCKING = 'thumb_sucking';
    case NAIL_BITING = 'nail_biting';
    case INVOLUNTARY_URINATION = 'involuntary_urination';
    case DISTRACTION = 'distraction';
    case LACK_OF_MOTIVATION = 'lack_of_motivation';
    case AGGRESSIVE_BEHAVIOR = 'aggressive_behavior';
    case SPEECH_PROBLEMS = 'speech_problems';
    case HYPERACTIVITY = 'hyperactivity';
    case OTHER = 'other';

    protected function getTranslationKey(): string
    {
        return 'student_behavioral_problems';
    }
}
