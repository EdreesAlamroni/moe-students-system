<?php

namespace App\Http\Requests\Administration\ClassPeriod;

use App\Enums\SchoolAcademicPeriod;
use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'academic_period' => [
                'required',
                Rule::enum(SchoolAcademicPeriod::class),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_periods', 'name')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('academic_period', $this->input('academic_period'))
                    ->ignore($this->route('classPeriod')),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'order' => [
                'required',
                'integer',
                'min:0',
            ],
            'is_break' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ];
    }

    public function getAttributes(): array
    {
        return $this->validated();
    }
}
