<?php

namespace App\Http\Resources\Warehouse;

use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SchoolPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SchoolPeriod $schoolPeriod */
        $schoolPeriod = $this->resource;

        return [
            'id' => $schoolPeriod->id,
            'uuid' => $schoolPeriod->uuid,
            'school_id' => $schoolPeriod->school_id,
            'education_monitor_id' => $schoolPeriod->education_monitor_id,
            'education_services_office_id' => $schoolPeriod->education_services_office_id,
            'name' => $schoolPeriod->name,
            'academic_period' => $schoolPeriod->academic_period->toArray(),
            'students_gender' => $schoolPeriod->students_gender?->toArray(),
            'is_morning_period' => $schoolPeriod->isMorningPeriod(),
            'is_evening_period' => $schoolPeriod->isEveningPeriod(),
            'educational_stages' => $this->whenLoaded('educationalStages', function (Collection $educationalStages): array {
                return $educationalStages->map(function (SchoolEducationalStage $educationalStage): array {
                    return [
                        'id' => $educationalStage->id,
                        'stage' => $educationalStage->stage->toArray(),
                    ];
                })->all();
            }),
            'grade_levels_count' => (int) ($schoolPeriod->grade_levels_count ?? 0),
            'classrooms_count' => (int) ($schoolPeriod->classrooms_count ?? 0),
            'students_count' => (int) ($schoolPeriod->students_count ?? 0),
        ];
    }
}
