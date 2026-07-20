<?php

namespace App\Http\Resources\School;

use App\Http\Resources\DirectModelCollection;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Classroom $classroom): array => [
            'id' => $classroom->id,
            'uuid' => $classroom->uuid,
            'grade_level' => $classroom->relationLoaded('gradeLevel')
                ? [
                    'id' => $classroom->gradeLevel->id,
                    'uuid' => $classroom->gradeLevel->uuid,
                    'name' => $classroom->gradeLevel->name,
                    'educational_stage' => $classroom->gradeLevel->educational_stage->toArray(),
                ]
                : null,
            'name' => $classroom->name,
            'students_count' => (int) ($classroom->students_count ?? 0),
        ])->all();
    }
}
