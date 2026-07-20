<?php

namespace App\Http\Resources\School;

use App\Enums\DayOfWeek;
use App\Models\Classroom;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ClassScheduleResource extends JsonResource
{
    /**
     * @var Collection<int, \App\Models\ClassPeriod>
     */
    private Collection $periods;

    /**
     * @var Collection<int, \App\Models\ClassSchedule>
     */
    private Collection $schedules;

    public function withContext(Collection $periods, Collection $schedules): self
    {
        $this->periods = $periods;
        $this->schedules = $schedules;

        return $this;
    }

    public function toArray(Request $request): array
    {
        /** @var Classroom $classroom */
        $classroom = $this->resource;

        $days = DayOfWeek::buildDays();
        $grid = $this->buildGrid();

        return [
            'classroom' => [
                'id' => $classroom->id,
                'uuid' => $classroom->uuid,
                'name' => $classroom->name,
                'grade_level' => $this->whenLoaded('gradeLevel', function (GradeLevel $gradeLevel): array {
                    return $gradeLevel->only(['name']);
                }),
            ],
            'days' => $days,
            'periods' => $this->periods,
            'grid' => $grid,
        ];
    }

    private function buildGrid(): array
    {
        $grid = [];

        foreach ($this->periods as $period) {
            foreach (DayOfWeek::schoolDays() as $day) {
                $item = $this->schedules
                    ->where('class_period_id', '=', $period->id)
                    ->where('day_of_week', '=', $day)
                    ->first();

                $grid[$period->id][$day->value] = match (true) {
                    $period->is_break => 'break',
                    $item !== null => ClassScheduleItemResource::make($item)->resolve(),
                    default => null,
                };
            }
        }

        return $grid;
    }
}
