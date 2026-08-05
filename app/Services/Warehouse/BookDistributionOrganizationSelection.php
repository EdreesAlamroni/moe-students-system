<?php

namespace App\Services\Warehouse;

use App\Models\EducationMonitor;
use App\Models\SchoolPeriod;

class BookDistributionOrganizationSelection
{
    public function resolve(array $selectedAttributes): array
    {
        $monitorId = $selectedAttributes['education_monitor_id'];
        $schoolPeriodId = $selectedAttributes['school_period_id'];

        $monitors = EducationMonitor::list(function ($query): void {
            $query->forCurrentWarehouse();
        }, ['warehouse_id']);

        $schoolPeriods = filled($monitorId)
            ? SchoolPeriod::list(function ($query) use ($monitorId): void {
                $query->forCurrentWarehouse()->where('education_monitor_id', '=', $monitorId);
            }, ['education_monitor_id'])
            : collect([]);

        return [
            'monitors' => $monitors,
            'schoolPeriods' => $schoolPeriods,
            'selected' => $selectedAttributes,
            'monitorId' => $monitorId,
            'schoolPeriodId' => $schoolPeriodId,
        ];
    }
}
