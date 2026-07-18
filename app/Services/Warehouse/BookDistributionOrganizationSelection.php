<?php

namespace App\Services\Warehouse;

use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Models\EducationMonitor;
use App\Models\School;
use Illuminate\Support\Collection;

class BookDistributionOrganizationSelection
{
    /**
     * @return array{
     *     monitors: Collection<int, mixed>,
     *     schools: Collection<int, mixed>,
     *     selected: array{education_monitor_id: int|null, school_id: int|null},
     *     monitorId: int|null,
     *     schoolId: int|null,
     * }
     */
    public function resolve(IndexRequest $request): array
    {
        $selectedAttributes = $request->getAttributes();
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
