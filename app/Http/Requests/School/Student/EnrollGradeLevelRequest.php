<?php

namespace App\Http\Requests\School\Student;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollGradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => [
                'required',
                Rule::exists(GradeLevel::class, 'id'),
                Rule::exists(GradeLevelSchoolPeriod::class, 'grade_level_id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_period_id', auth('school')->user()->organization_id),
            ],
        ];
    }
}
