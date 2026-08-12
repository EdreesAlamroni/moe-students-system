<?php

namespace App\Http\Requests\School;

use App\Models\SchoolPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeriodSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        $user = auth('school')->user();

        return [
            'school_period_id' => [
                'required',
                'integer',
                Rule::exists(SchoolPeriod::class, 'id'),
                Rule::in($user->schoolPeriods()->pluck('school_periods.id')->all()),
            ],
        ];
    }
}
