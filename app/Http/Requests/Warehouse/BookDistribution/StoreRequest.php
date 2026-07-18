<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use App\Models\AcademicYear;
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
        $academicYearId = AcademicYear::currentId();

        return [
            'education_monitor_id' => [
                'required',
                'integer',
                Rule::exists('education_monitors', 'id')->where('warehouse_id', $warehouseId),
            ],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where('education_monitor_id', $this->integer('education_monitor_id')),
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
                Rule::exists('grade_level_school', 'grade_level_id')
                    ->where('school_id', $this->integer('school_id'))
                    ->where('academic_year_id', $academicYearId),
            ],
        ];
    }

    /**
     * @return array{education_monitor_id: int, school_id: int, grade_level_ids: array<int, int>}
     */
    public function getAttributes(): array
    {
        return [
            'education_monitor_id' => $this->integer('education_monitor_id'),
            'school_id' => $this->integer('school_id'),
            'grade_level_ids' => array_map('intval', $this->input('grade_level_ids', [])),
        ];
    }
}
