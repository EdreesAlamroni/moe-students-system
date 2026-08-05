<?php

namespace App\Http\Resources\EducationMonitor;

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
            'office' => $school->relationLoaded('office')
                ? $school->office?->only(['id', 'uuid', 'name'])
                : null,
            'academic_period_label' => $school->academic_period_label,
            'students_count' => $school->relationLoaded('periods')
                ? (int) $school->periods->sum('students_count')
                : 0,
        ])->all();
    }
}
