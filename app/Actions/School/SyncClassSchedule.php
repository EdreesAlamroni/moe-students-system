<?php

namespace App\Actions\School;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;

class SyncClassSchedule
{
    public function execute(Classroom $classroom, array $items): void
    {
        DB::transaction(function () use ($classroom, $items) {
            $keepIds = [];

            foreach ($items as $item) {
                if (blank($item['subject_id'])) {
                    continue;
                }

                $schedule = ClassSchedule::query()->updateOrCreate([
                    'classroom_id' => $classroom->id,
                    'class_period_id' => $item['class_period_id'],
                    'day_of_week' => $item['day_of_week'],
                    'academic_year_id' => $item['academic_year_id'],
                    'school_id' => $item['school_id'],
                ], [
                    'subject_id' => $item['subject_id'],
                    'notes' => $item['notes'],
                ]);

                $keepIds[] = $schedule->id;
            }

            ClassSchedule::query()
                ->forClassroom($classroom)
                ->forCurrentSchoolAndAcademicYear()
                ->whereNotIn('id', $keepIds)
                ->delete();
        });
    }
}
