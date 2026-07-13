<?php

namespace App\Http\Resources\Administration;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
            'academic_period' => $school->academic_period->toArray(),
            'students_gender' => $school->students_gender->toArray(),
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
            'educational_stages' => $this->whenLoaded('educationalStages', function (Collection $educationalStages): array {
                return $educationalStages->map(function (SchoolEducationalStage $educationalStage): array {
                    return [
                        'id' => $educationalStage->id,
                        'stage' => $educationalStage->stage->toArray(),
                    ];
                })->all();
            }),
            'grade_levels_count' => $this->whenHas('grade_levels_count', intval($school->grade_levels_count ?? 0), 0),
            'classrooms_count' => $this->whenHas('classrooms_count', intval($school->classrooms_count ?? 0), 0),
            'students_count' => $this->whenHas('students_count', intval($school->students_count ?? 0), 0),
        ];
    }
}
