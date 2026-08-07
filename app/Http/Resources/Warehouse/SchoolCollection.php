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
            'number' => $school->number,
            'name' => $school->name,
            'type' => $school->type->toArray(),
            'monitor' => $school->relationLoaded('monitor')
                ? $school->monitor->only(['id', 'uuid', 'name'])
                : null,
            'academic_period_label' => $school->academic_period_label,
            'students_count' => $school->relationLoaded('periods')
                ? (int) $school->periods->sum('students_count')
                : 0,
        ])->all();
    }
}
