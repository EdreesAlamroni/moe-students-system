<?php

namespace App\Http\Resources\Administration;

use App\Models\ClassPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ClassPeriod $classPeriod */
        $classPeriod = $this->resource;

        return [
            'id' => $classPeriod->id,
            'uuid' => $classPeriod->uuid,
            'academic_period' => $classPeriod->academic_period->toArray(),
            'name' => $classPeriod->name,
            'start_time' => $classPeriod->start_time->format('H:i'),
            'end_time' => $classPeriod->end_time->format('H:i'),
            'type' => $classPeriod->type,
            'order' => $classPeriod->order,
            'schedules_count' => (int) ($classPeriod->schedules_count ?? 0),
        ];
    }
}
