<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (AcademicYear $academicYear): array => [
            'id' => $academicYear->id,
            'uuid' => $academicYear->uuid,
            'name' => $academicYear->name,
            'start_date' => $academicYear->start_date->toDateString(),
            'end_date' => $academicYear->end_date->toDateString(),
            'is_active' => $academicYear->is_active,
            'status' => $academicYear->status,
        ])->all();
    }
}
