<?php

namespace App\Http\Resources\School;

use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GradeLevel $gradeLevel */
        $gradeLevel = $this->resource;

        return [
            'id' => $gradeLevel->id,
            'uuid' => $gradeLevel->uuid,
            'name' => $gradeLevel->name,
            'educational_stage' => $gradeLevel->educational_stage->toArray(),
            'students_count' => (int) ($gradeLevel->students_count ?? 0),
            'classrooms_count' => (int) ($gradeLevel->classrooms_count ?? 0),
        ];
    }
}
