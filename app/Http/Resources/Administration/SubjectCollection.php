<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Subject $subject): array => [
            'id' => $subject->id,
            'uuid' => $subject->uuid,
            'grade_level_id' => $subject->grade_level_id,
            'grade_level' => $subject->relationLoaded('gradeLevel')
                ? GradeLevelSummaryResource::make($subject->gradeLevel)
                : null,
            'name' => $subject->name,
            'code' => $subject->code,
        ])->all();
    }
}
