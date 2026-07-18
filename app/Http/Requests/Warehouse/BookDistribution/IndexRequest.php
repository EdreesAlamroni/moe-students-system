<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
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
            'school_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(function (): bool {
                    return ! $this->filled('education_monitor_id');
                }),
                Rule::exists('schools', 'id')->where('education_monitor_id', $this->integer('education_monitor_id')),
            ],
        ];
    }

    /**
     * @return array{education_monitor_id: int|null, school_id: int|null}
     */
    public function getAttributes(): array
    {
        $monitorId = $this->integer('education_monitor_id') ?: null;
        $schoolId = $this->integer('school_id') ?: null;

        return [
            'education_monitor_id' => $monitorId,
            'school_id' => $schoolId,
        ];
    }
}
