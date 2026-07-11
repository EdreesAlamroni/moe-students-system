<?php

namespace App\Http\Requests\Administration\EducationMonitor;

use App\Models\EducationMonitor;
use App\Models\Municipal;
use App\Rules\LatitudeRule;
use App\Rules\LibyanPhoneNumberRule;
use App\Rules\LongitudeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        /** @var EducationMonitor $monitor */
        $monitor = $this->route('monitor');

        return [
            'municipal_id' => [
                'required',
                'integer',
                Rule::exists(Municipal::class, 'id'),
                Rule::unique(EducationMonitor::class, 'municipal_id')->ignore($monitor),
            ],
            'phone_number' => [
                'sometimes',
                'nullable',
                'string',
                new LibyanPhoneNumberRule,
                Rule::unique(EducationMonitor::class, 'phone_number')->ignore($monitor),
            ],
            'whatsapp_phone_number' => [
                'sometimes',
                'nullable',
                'string',
                new LibyanPhoneNumberRule,
                Rule::unique(EducationMonitor::class, 'whatsapp_phone_number')->ignore($monitor),
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

        $this->merge([
            'add_location_to_map' => $addLocationToMap,
            'latitude' => $addLocationToMap ? $this->input('latitude') : null,
            'longitude' => $addLocationToMap ? $this->input('longitude') : null,
        ]);
    }

    public function getAttributes(): array
    {
        return Arr::except($this->validated(), ['add_location_to_map']);
    }
}
