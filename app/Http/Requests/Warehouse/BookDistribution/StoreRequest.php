<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use App\Models\AcademicYear;
use App\Models\SchoolPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('education_monitors', 'id')->where('warehouse_id', $warehouseId),
            ],
            'school_period_id' => [
                'required',
                'integer',
                Rule::exists(SchoolPeriod::class, 'id')
                    ->where('education_monitor_id', $this->integer('education_monitor_id')),
            ],
            'grade_level_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'grade_level_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('grade_level_school_period', 'grade_level_id')
                    ->where('school_period_id', $this->integer('school_period_id'))
                    ->where('academic_year_id', AcademicYear::currentId()),
            ],
        ];
    }

    /**
     * @return array{education_monitor_id: int, school_period_id: int, grade_level_ids: array<int, int>}
     */
    public function getAttributes(): array
    {
        return [
            'education_monitor_id' => $this->integer('education_monitor_id'),
            'school_period_id' => $this->integer('school_period_id'),
            'grade_level_ids' => array_map('intval', $this->input('grade_level_ids', [])),
        ];
    }
}
