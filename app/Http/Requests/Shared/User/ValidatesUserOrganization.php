<?php

namespace App\Http\Requests\Shared\User;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\Warehouse;
use Illuminate\Validation\Rule;

trait ValidatesUserOrganization
{
    protected function warehouseIdRules(): array
    {
        return [
            'required',
            Rule::exists(Warehouse::class, 'id'),
        ];
    }

    protected function educationMonitorIdRules(): array
    {
        return [
            'required',
            Rule::exists(EducationMonitor::class, 'id'),
        ];
    }

    protected function educationServicesOfficeIdRules(?int $monitorId): array
    {
        return [
            'required',
            Rule::exists(EducationServicesOffice::class, 'id')
                ->where('education_monitor_id', $monitorId),
        ];
    }

    protected function schoolIdRulesForMonitor(?int $monitorId): array
    {
        return [
            'required',
            'integer',
            Rule::exists(School::class, 'id')
                ->where('education_monitor_id', $monitorId),
        ];
    }

    protected function schoolIdRulesForOffice(?int $officeId): array
    {
        return [
            'required',
            'integer',
            Rule::exists(School::class, 'id')
                ->where('education_services_office_id', $officeId),
        ];
    }

    protected function schoolIdExistsRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists(School::class, 'id'),
        ];
    }
}
