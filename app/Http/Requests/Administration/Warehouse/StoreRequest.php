<?php

namespace App\Http\Requests\Administration\Warehouse;

use App\Models\EducationMonitor;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'education_monitor_ids' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'education_monitor_ids.*' => [
                'integer',
                Rule::exists(EducationMonitor::class, 'id')->where(function ($query): void {
                    $query->whereNull('warehouse_id');
                }),
            ],
            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'add_location_to_map' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'latitude' => [
                Rule::requiredIf(function () {
                    return $this->boolean('add_location_to_map');
                }),
                'sometimes',
                'nullable',
                new LatitudeRule,
            ],
            'longitude' => [
                Rule::requiredIf(function () {
                    return $this->boolean('add_location_to_map');
                }),
                'sometimes',
                'nullable',
                new LongitudeRule,
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        $addLocationToMap = $this->boolean('add_location_to_map');
        $monitorIds = json_decode($this->input('education_monitor_ids', '[]'), true) ?: [];

        $this->merge([
            'add_location_to_map' => $addLocationToMap,
            'latitude' => $addLocationToMap ? $this->input('latitude') : null,
            'longitude' => $addLocationToMap ? $this->input('longitude') : null,
            'education_monitor_ids' => $monitorIds,
        ]);
    }

    public function getAttributes(): array
    {
        return Arr::except($this->validated(), ['add_location_to_map', 'education_monitor_ids']);
    }
}
