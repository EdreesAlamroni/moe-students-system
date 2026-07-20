<?php

namespace App\Http\Resources\School;

use App\Models\Classroom;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Classroom $classroom */
        $classroom = $this->resource;

        return [
            'id' => $classroom->id,
            'uuid' => $classroom->uuid,
            'grade_level' => $this->whenLoaded('gradeLevel', function (GradeLevel $gradeLevel): array {
                return [
                    'id' => $gradeLevel->id,
                    'uuid' => $gradeLevel->uuid,
                    'name' => $gradeLevel->name,
                    'educational_stage' => $gradeLevel->educational_stage->toArray(),
                ];
            }),
            'name' => $classroom->name,
            'capacity' => $classroom->capacity,
            'students_count' => (int) ($classroom->students_count ?? 0),
            'schedules_count' => (int) ($classroom->schedules_count ?? 0),
        ];
    }
}
