<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use App\Models\AcademicYear;
use App\Models\SchoolPeriod;
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

        return [
            'education_monitor_id' => [
                'nullable',
                'integer',
                Rule::exists('education_monitors', 'id')->where('warehouse_id', $warehouseId),
            ],
            'school_period_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('education_monitor_id');
                }),
                Rule::exists(SchoolPeriod::class, 'id')
                    ->where('education_monitor_id', $this->integer('education_monitor_id')),
            ],
            'grade_level_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('school_period_id');
                }),
                Rule::exists('grade_level_school_period', 'grade_level_id')
                    ->where('school_period_id', $this->integer('school_period_id'))
                    ->where('academic_year_id', AcademicYear::currentId()),
            ],
        ];
    }

    /**
     * @return array{education_monitor_id: int|null, school_period_id: int|null, grade_level_id: int|null}
     */
    public function getAttributes(): array
    {
        $monitorId = $this->integer('education_monitor_id') ?: null;
        $schoolPeriodId = $this->integer('school_period_id') ?: null;
        $gradeLevelId = $this->integer('grade_level_id') ?: null;

        return [
            'education_monitor_id' => $monitorId,
            'school_period_id' => $schoolPeriodId,
            'grade_level_id' => $gradeLevelId,
        ];
    }
}
