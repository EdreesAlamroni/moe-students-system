<?php

namespace App\Http\Resources\Warehouse;

use App\Http\Resources\DirectModelCollection;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (School $school): array => [
            'id' => $school->id,
            'uuid' => $school->uuid,
            'serial_number' => $school->serial_number,
            'name' => $school->name,
            'type' => $school->type->toArray(),
            'academic_period' => $school->academic_period->toArray(),
            'monitor' => $school->relationLoaded('monitor')
                ? $school->monitor->only(['id', 'uuid', 'name'])
                : null,
            'students_count' => (int) ($school->students_count ?? 0),
        ])->all();
    }
}
