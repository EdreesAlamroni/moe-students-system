<?php

namespace App\Http\Requests\School\Classroom;

use App\Enums\SchoolEducationalStageEnum;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\SchoolEducationalStage;
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
            'educational_stage' => [
                'required',
                Rule::enum(SchoolEducationalStageEnum::class),
                Rule::exists(SchoolEducationalStage::class, 'stage')
                    ->where('school_period_id', $schoolPeriodId),
            ],
            'grade_level_id' => [
                'required',
                Rule::exists(GradeLevel::class, 'id')
                    ->where('educational_stage', $this->input('educational_stage')),
                Rule::exists(GradeLevelSchoolPeriod::class, 'grade_level_id')
                    ->where('academic_year_id', $currentAcademicYearId)
                    ->where('school_period_id', $schoolPeriodId),
            ],
            'name' => [
                'required',
                'string',
                Rule::in(array_map('strval', range(1, 12))),
                Rule::unique('classrooms', 'name')
                    ->where('academic_year_id', $currentAcademicYearId)
                    ->where('school_period_id', $schoolPeriodId)
                    ->where('grade_level_id', $this->integer('grade_level_id')),
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function getAttributes(): array
    {
        return [
            [
                'academic_year_id' => AcademicYear::currentId(),
                'school_period_id' => auth('school')->user()->organization_id,
                'grade_level_id' => $this->integer('grade_level_id'),
                'name' => $this->input('name'),
            ],
            [
                'capacity' => $this->integer('capacity'),
            ],
        ];
    }
}
