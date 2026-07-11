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
            'offices_count' => $monitor->offices_count,
            'schools_count' => $monitor->schools_count,
            'students_count' => $monitor->students_count,
        ])->all();
    }
}
