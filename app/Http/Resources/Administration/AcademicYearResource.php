<?php

namespace App\Http\Resources\Administration;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AcademicYear $academicYear */
        $academicYear = $this->resource;

        return [
            'id' => $academicYear->id,
            'uuid' => $academicYear->uuid,
            'name' => $academicYear->name,
            'start_date' => $academicYear->start_date->toDateString(),
            'end_date' => $academicYear->end_date->toDateString(),
            'is_active' => $academicYear->is_active,
            'status' => $academicYear->status,
        ];
    }
}
