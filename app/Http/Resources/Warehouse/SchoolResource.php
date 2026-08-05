<?php

namespace App\Http\Resources\Warehouse;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var School $school */
        $school = $this->resource;

        return [
            'id' => $school->id,
            'uuid' => $school->uuid,
            'serial_number' => $school->serial_number,
            'name' => $school->name,
            'type' => $school->type->toArray(),
            'educational_company_name' => $school->educational_company_name,
            'branch_type' => $school->branch_type?->toArray(),
            'building_type' => $school->building_type?->toArray(),
            'is_public' => $school->isPublic(),
            'is_private' => $school->isPrivate(),
            'monitor' => $this->whenLoaded('monitor', function (EducationMonitor $monitor): array {
                return $monitor->only(['id', 'uuid', 'name']);
            }),
            'office' => $this->whenLoaded('office', function (EducationServicesOffice $office): array {
                return $office->only(['id', 'uuid', 'name']);
            }),
            'academic_period_label' => $school->academic_period_label,
            'periods' => $this->whenLoaded('periods', function () use ($request, $school): array {
                return SchoolPeriodResource::collection($school->periods)->resolve($request);
            }),
            'grade_levels_count' => $this->aggregatePeriodCount($school, 'grade_levels_count'),
            'classrooms_count' => $this->aggregatePeriodCount($school, 'classrooms_count'),
            'students_count' => $this->aggregatePeriodCount($school, 'students_count'),
        ];
    }

    private function aggregatePeriodCount(School $school, string $attribute): int
    {
        if ($school->hasAttribute($attribute)) {
            return (int) $school->getAttribute($attribute);
        }

        if (! $school->relationLoaded('periods')) {
            return 0;
        }

        return (int) $school->periods->sum($attribute);
    }
}
