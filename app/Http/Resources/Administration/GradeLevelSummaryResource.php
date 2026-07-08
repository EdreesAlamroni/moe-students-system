<?php

namespace App\Http\Resources\Administration;

use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeLevelSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GradeLevel $gradeLevel */
        $gradeLevel = $this->resource;

        return [
            'id' => $gradeLevel->id,
            'name' => $gradeLevel->name,
        ];
    }
}
