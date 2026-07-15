<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\ClassPeriod;
use Illuminate\Http\Request;

class ClassPeriodCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (ClassPeriod $classPeriod) => [
            'id' => $classPeriod->id,
            'uuid' => $classPeriod->uuid,
            'academic_period' => $classPeriod->academic_period->toArray(),
            'name' => $classPeriod->name,
            'start_time' => $classPeriod->start_time->format('H:i'),
            'end_time' => $classPeriod->end_time->format('H:i'),
        ])->all();
    }
}
