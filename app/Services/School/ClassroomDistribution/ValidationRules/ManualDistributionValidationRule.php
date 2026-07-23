<?php

namespace App\Services\School\ClassroomDistribution\ValidationRules;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchool;
use App\Models\Student;
use App\Services\School\ClassroomDistribution\Contracts\DistributionValidationRuleContract;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManualDistributionValidationRule implements DistributionValidationRuleContract
{
    public function rules(Request $request): array
    {
        return [
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists(GradeLevel::class, 'id'),
                Rule::exists(GradeLevelSchool::class, 'grade_level_id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_id', auth('school')->user()->organization_id),
            ],
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists(Classroom::class, 'id')
                    ->where('school_id', auth('school')->user()->organization_id)
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('grade_level_id', $request->integer('grade_level_id')),
            ],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'integer',
                Rule::exists(Student::class, 'id')
                    ->where('school_id', auth('school')->user()->organization_id),
            ],
        ];
    }
}
