<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('warehouse')->check();
    }

    public function rules(): array
    {
        $warehouseId = auth('warehouse')->user()->organization_id;
        $academicYearId = AcademicYear::currentId();

        return [
            'education_monitor_id' => [
                'nullable',
                'integer',
                Rule::exists('education_monitors', 'id')->where('warehouse_id', $warehouseId),
            ],
            'school_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('education_monitor_id');
                }),
                Rule::exists('schools', 'id')->where('education_monitor_id', $this->integer('education_monitor_id')),
            ],
            'grade_level_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('school_id');
                }),
                Rule::exists('grade_level_school', 'grade_level_id')
                    ->where('school_id', $this->integer('school_id'))
                    ->where('academic_year_id', $academicYearId),
            ],
        ];
    }

    /**
     * @return array{education_monitor_id: int|null, school_id: int|null, grade_level_id: int|null}
     */
    public function getAttributes(): array
    {
        $monitorId = $this->integer('education_monitor_id') ?: null;
        $schoolId = $this->integer('school_id') ?: null;
        $gradeLevelId = $this->integer('grade_level_id') ?: null;

        return [
            'education_monitor_id' => $monitorId,
            'school_id' => $schoolId,
            'grade_level_id' => $gradeLevelId,
        ];
    }
}
