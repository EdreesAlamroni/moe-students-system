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
        $schoolId = auth('school')->user()->organization_id;
        $academicYearId = AcademicYear::currentId();

        return [
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists('grade_level_school', 'grade_level_id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId),
                Rule::exists(BookDistribution::class, 'grade_level_id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId),
            ],
            'classroom_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists(Classroom::class, 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId)
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
                Rule::exists('students', 'id')->where('school_id', $schoolId),
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
