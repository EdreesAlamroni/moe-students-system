<?php

namespace App\Http\Resources\School;

use App\Models\ClassSchedule;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ClassSchedule $classSchedule */
        $classSchedule = $this->resource;

        return [
            'id' => $classSchedule->id,
            'uuid' => $classSchedule->uuid,
            'subject_id' => $classSchedule->subject_id,
            'subject' => $this->whenLoaded('subject', function (Subject $subject): array {
                return $subject->only(['name']);
            }),
            'notes' => $classSchedule->notes,
        ];
    }
}
