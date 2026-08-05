<?php

namespace App\Http\Resources\Warehouse;

use App\Http\Resources\DirectModelCollection;
use App\Models\GradeLevel;
use Illuminate\Http\Request;

class GradeLevelCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(function (GradeLevel $gradeLevel): array {
            $payload = [
                'id' => $gradeLevel->id,
                'uuid' => $gradeLevel->uuid,
                'name' => $gradeLevel->name,
                'educational_stage' => $gradeLevel->educational_stage->toArray(),
                'students_count' => (int) ($gradeLevel->students_count ?? 0),
            ];

            if ($gradeLevel->relationLoaded('schoolPeriods') && ($schoolPeriod = $gradeLevel->schoolPeriods->first())) {
                $payload['academic_period'] = $schoolPeriod->academic_period->toArray();
            }

            return $payload;
        })->all();
    }
}
