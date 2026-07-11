<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\EducationMonitor;
use Illuminate\Http\Request;

class EducationMonitorCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (EducationMonitor $monitor): array => [
            'id' => $monitor->id,
            'uuid' => $monitor->uuid,
            'name' => $monitor->name,
            'offices_count' => intval($monitor->offices_count ?? 0),
            'schools_count' => intval($monitor->schools_count ?? 0),
            'students_count' => intval($monitor->students_count ?? 0),
        ])->all();
    }
}
