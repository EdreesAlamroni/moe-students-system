<?php

namespace App\Rules;

use App\Models\SchoolPeriod;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class SchoolPeriodMembership implements ValidationRule
{
    public function __construct(
        private readonly ?int $schoolId,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->schoolId === null) {
            $fail('validation.required')->translate(['attribute' => __('validation.attributes.school_id')]);

            return;
        }

        $periodIds = SchoolPeriod::query()
            ->where('school_id', '=', $this->schoolId)
            ->orderedByAcademicPeriod()
            ->pluck('id')
            ->all();

        if (empty($periodIds)) {
            $fail('validation.exists')->translate(['attribute' => __('validation.attributes.school_id')]);

            return;
        }

        if (count($periodIds) === 1) {
            return;
        }

        if (! is_array($value) || $value === []) {
            $fail('validation.required')->translate(['attribute' => __('validation.attributes.school_period_ids')]);

            return;
        }

        $requestedIds = array_values(array_unique(array_map(intval(...), $value)));
        $invalidIds = array_diff($requestedIds, $periodIds);

        if ($invalidIds !== []) {
            $fail('validation.in')->translate(['attribute' => __('validation.attributes.school_period_ids')]);
        }
    }
}
