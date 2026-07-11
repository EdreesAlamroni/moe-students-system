<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\EducationServicesOffice;
use Illuminate\Http\Request;

class EducationServicesOfficeCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (EducationServicesOffice $office): array => [
            'id' => $office->id,
            'uuid' => $office->uuid,
            'education_monitor_id' => $office->education_monitor_id,
            'name' => $office->name,
            'monitor' => $office->relationLoaded('monitor')
                ? $office->monitor->only(['id', 'uuid', 'name'])
                : null,
            'schools_count' => intval($office->schools_count ?? 0),
            'students_count' => intval($office->students_count ?? 0),
        ])->all();
    }
}
