<?php

namespace App\Http\Resources\Administration;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Subject $subject */
        $subject = $this->resource;

        return [
            'id' => $subject->id,
            'uuid' => $subject->uuid,
            'grade_level_id' => $subject->grade_level_id,
            'grade_level' => $this->whenLoaded('gradeLevel', function (GradeLevel $gradeLevel) {
                return $gradeLevel->only(['id', 'uuid', 'name']);
            }),
            'name' => $subject->name,
            'code' => $subject->code,
            'included_in_total_score' => $subject->included_in_total_score,
            'needs_lab' => $subject->needs_lab,
            'description' => $subject->description,
            'included_in_total_score_label' => $subject->included_in_total_score_label,
            'needs_lab_label' => $subject->needs_lab_label,
        ];
    }
}
