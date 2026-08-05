<?php

namespace App\Http\Requests\School\BookDistribution;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        $currentAcademicYearId = AcademicYear::currentId();

        $schoolPeriodId = auth('school')->user()->organization_id;

        return [
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists('grade_level_school_period', 'grade_level_id')
                    ->where('school_period_id', $schoolPeriodId)
                    ->where('academic_year_id', $currentAcademicYearId),
                Rule::exists(BookDistribution::class, 'grade_level_id')
                    ->where('school_period_id', $schoolPeriodId)
                    ->where('academic_year_id', $currentAcademicYearId),
            ],
            'classroom_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists(Classroom::class, 'id')
                    ->where('school_period_id', $schoolPeriodId)
                    ->where('academic_year_id', $currentAcademicYearId)
                    ->where('grade_level_id', $this->integer('grade_level_id')),
            ],
            'student_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'student_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('students', 'id')->where('school_period_id', $schoolPeriodId),
            ],
        ];
    }

    public function getAttributes(): array
    {
        $classroomId = $this->integer('classroom_id') ?: null;

        return [
            'grade_level_id' => $this->integer('grade_level_id'),
            'classroom_id' => $classroomId,
            'student_ids' => array_map('intval', $this->input('student_ids', [])),
        ];
    }
}
