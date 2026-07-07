<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\GradeLevel;
use Illuminate\Http\Request;

class GradeLevelCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (GradeLevel $gradeLevel): array => [
            'id' => $gradeLevel->id,
            'uuid' => $gradeLevel->uuid,
            'name' => $gradeLevel->name,
            'educational_stage' => $gradeLevel->educational_stage->toArray(),
        ])->all();
    }
}
