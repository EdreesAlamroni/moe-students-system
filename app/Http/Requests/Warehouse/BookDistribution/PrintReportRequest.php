<?php

namespace App\Http\Requests\Warehouse\BookDistribution;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintReportRequest extends FormRequest
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
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where('education_monitor_id', $this->integer('education_monitor_id')),
            ],
        ];
    }
}
