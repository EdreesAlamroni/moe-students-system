<?php

namespace App\Http\Requests\Shared\School;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use Illuminate\Validation\Rule;

trait ValidatesSchoolOrganization
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function educationMonitorRules(): array
    {
        return [
            'education_monitor_id' => [
                'required',
                'integer',
                Rule::exists(EducationMonitor::class, 'id'),
            ],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function educationServicesOfficeRules(): array
    {
        return [
            'education_services_office_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->monitorHasOffices();
                }),
                Rule::exists(EducationServicesOffice::class, 'id')
                    ->where('education_monitor_id', $this->educationMonitorId()),
            ],
        ];
    }

    protected function educationMonitorId(): ?int
    {
        $monitorId = $this->input('education_monitor_id');

        if (blank($monitorId)) {
            return null;
        }

        return (int) $monitorId;
    }

    protected function educationServicesOfficeId(): ?int
    {
        $officeId = $this->input('education_services_office_id');

        if (blank($officeId)) {
            return null;
        }

        return (int) $officeId;
    }

    protected function resolvedEducationServicesOfficeId(): ?int
    {
        if (! $this->monitorHasOffices()) {
            return null;
        }

        return $this->educationServicesOfficeId();
    }

    protected function monitorHasOffices(): bool
    {
        $monitorId = $this->educationMonitorId();

        if (blank($monitorId)) {
            return false;
        }

        return EducationServicesOffice::query()
            ->where('education_monitor_id', '=', $monitorId)
            ->exists();
    }
}
