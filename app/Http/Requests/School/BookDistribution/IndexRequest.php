<?php

namespace App\Http\Requests\School\BookDistribution;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
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
                'nullable',
                'integer',
                Rule::exists('grade_level_school', 'grade_level_id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId),
            ],
            'classroom_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('grade_level_id');
                }),
                Rule::exists(Classroom::class, 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('grade_level_id', $this->integer('grade_level_id')),
            ],
        ];
    }

    public function getAttributes(): array
    {
        $gradeLevelId = $this->integer('grade_level_id') ?: null;
        $classroomId = $this->integer('classroom_id') ?: null;

        return [
            'grade_level_id' => $gradeLevelId,
            'classroom_id' => $classroomId,
        ];
    }
}
