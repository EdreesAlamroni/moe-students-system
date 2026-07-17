<?php

namespace App\Http\Resources\Warehouse;

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
            'offices_count' => (int) ($monitor->offices_count ?? 0),
            'schools_count' => (int) ($monitor->schools_count ?? 0),
            'students_count' => (int) ($monitor->students_count ?? 0),
        ])->all();
    }
}
