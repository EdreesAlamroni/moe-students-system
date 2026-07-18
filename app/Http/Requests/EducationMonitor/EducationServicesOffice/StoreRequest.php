<?php

namespace App\Http\Requests\EducationMonitor\EducationServicesOffice;

use App\Models\EducationServicesOffice;
use App\Models\User;
use App\Rules\LatitudeRule;
use App\Rules\LibyanPhoneNumberRule;
use App\Rules\LongitudeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone_number' => [
                'sometimes',
                'nullable',
                'string',
                new LibyanPhoneNumberRule,
                Rule::unique(EducationServicesOffice::class, 'phone_number'),
            ],
            'whatsapp_phone_number' => [
                'sometimes',
                'nullable',
                'string',
                new LibyanPhoneNumberRule,
                Rule::unique(EducationServicesOffice::class, 'whatsapp_phone_number'),
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
        return Arr::merge(
            Arr::except($this->validated(), ['add_location_to_map']),
            ['education_monitor_id' => $this->currentMonitorId()],
        );
    }

    private function currentMonitorId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_monitor');

        return $user?->organization_id;
    }
}
