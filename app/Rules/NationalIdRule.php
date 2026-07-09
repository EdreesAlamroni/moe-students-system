<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class NationalIdRule implements ValidationRule
{
    public function __construct(
        public ?string $dateOfBirth = null,
        public ?string $gender = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($this->dateOfBirth)) {
            $fail('validation.required')->translate(['attribute' => __('validation.attributes.date_of_birth')]);
        }

        if (blank($this->gender)) {
            $fail('validation.required')->translate(['attribute' => __('validation.attributes.gender')]);
        }

        $checkGenderType = ($this->gender == 'male') ? Str::of($value)->startsWith('1') : Str::of($value)->startsWith('2');

        if ($checkGenderType == false) {
            $fail('validation.nid_starts_with')->translate();
        }

        $yearOfBirth = date('Y', strtotime($this->dateOfBirth));

        if ($yearOfBirth != substr($value, 1, 4)) {
            $fail('validation.nid_year_of_birth')->translate();
        }

        if (strlen($value) != 12) {
            $fail('validation.digits')->translate(['digits' => 12]);
        }
    }
}
