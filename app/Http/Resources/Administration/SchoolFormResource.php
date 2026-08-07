<?php

namespace App\Http\Resources\Administration;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var School $school */
        $school = $this->resource;

        return [
            'id' => $school->id,
            'uuid' => $school->uuid,
            'number' => $school->number,
            'education_monitor_id' => $school->education_monitor_id,
            'monitor' => $this->whenLoaded('monitor', function (EducationMonitor $monitor) {
                return $monitor->only(['id', 'uuid', 'name']);
            }),
            'education_services_office_id' => $school->education_services_office_id,
            'office' => $this->whenLoaded('office', function (EducationServicesOffice $office) {
                return $office->only(['id', 'uuid', 'name']);
            }),
            'name' => $school->name,
            'type' => $school->type->toArray(),
            'is_private' => $school->isPrivate(),
            'educational_company_name' => $school->educational_company_name,
            'branch_type' => $school->branch_type?->toArray(),
            'building_type' => $school->building_type?->toArray(),
        ];
    }
}
