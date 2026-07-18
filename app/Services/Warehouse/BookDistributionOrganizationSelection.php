<?php

namespace App\Services\Warehouse;

use App\Models\EducationMonitor;
use App\Models\School;
use Illuminate\Support\Collection;

class BookDistributionOrganizationSelection
{
    /**
     * @param  array{education_monitor_id: int|null, school_id: int|null, grade_level_id?: int|null}  $selectedAttributes
     * @return array{
     *     monitors: Collection<int, mixed>,
     *     schools: Collection<int, mixed>,
     *     selected: array{education_monitor_id: int|null, school_id: int|null, grade_level_id?: int|null},
     *     monitorId: int|null,
     *     schoolId: int|null,
     * }
     */
    public function resolve(array $selectedAttributes): array
    {
        $monitorId = $selectedAttributes['education_monitor_id'];
        $schoolId = $selectedAttributes['school_id'];

        $monitors = EducationMonitor::list(function ($query): void {
            $query->forCurrentWarehouse();
        }, ['warehouse_id']);

        $schools = filled($monitorId)
            ? School::list(function ($query) use ($monitorId): void {
                $query->forCurrentWarehouse()->where('education_monitor_id', '=', $monitorId);
            }, ['education_monitor_id'])
            : collect([]);

        return [
            'monitors' => $monitors,
            'schools' => $schools,
            'selected' => $selectedAttributes,
            'monitorId' => $monitorId,
            'schoolId' => $schoolId,
        ];
    }
}
