<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\SchoolPeriod;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassScheduleSeeder extends Seeder
{
    private const INSERT_CHUNK_SIZE = 500;

    public function run(): void
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if ($currentAcademicYearId === null) {
            return;
        }

        if (! ClassPeriod::query()->where('academic_year_id', '=', $currentAcademicYearId)->exists()) {
            $this->call(ClassPeriodSeeder::class);
        }

        $schoolPeriods = SchoolPeriod::query()->get(['id', 'academic_period']);

        if ($schoolPeriods->isEmpty()) {
            return;
        }

        $classroomsBySchoolPeriod = Classroom::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->get(['id', 'school_period_id', 'grade_level_id'])
            ->groupBy('school_period_id');

        if ($classroomsBySchoolPeriod->isEmpty()) {
            return;
        }

        $periodsByAcademicPeriod = ClassPeriod::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->where('is_break', '=', false)
            ->ordered()
            ->get(['id', 'academic_period'])
            ->groupBy(fn (ClassPeriod $period): string => $period->academic_period->value);

        $subjectsByGradeLevel = Subject::query()
            ->get(['id', 'grade_level_id'])
            ->groupBy('grade_level_id');

        $seededClassroomIds = ClassSchedule::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->distinct()
            ->pluck('classroom_id');

        $days = DayOfWeek::schoolDays();
        $timestamp = now();
        $records = [];

        foreach ($schoolPeriods as $schoolPeriod) {
            $classrooms = $classroomsBySchoolPeriod->get($schoolPeriod->id, collect());

            if ($classrooms->isEmpty()) {
                continue;
            }

            $periods = $periodsByAcademicPeriod->get($schoolPeriod->academic_period->value, collect());

            if ($periods->isEmpty()) {
                continue;
            }

            foreach ($classrooms as $classroom) {
                if ($seededClassroomIds->contains($classroom->id)) {
                    continue;
                }

                $subjects = $subjectsByGradeLevel->get($classroom->grade_level_id, collect());

                if ($subjects->isEmpty()) {
                    continue;
                }

                foreach ($periods as $period) {
                    foreach ($days as $day) {
                        $records[] = [
                            'uuid' => Str::uuid7()->toString(),
                            'school_period_id' => $schoolPeriod->id,
                            'academic_year_id' => $currentAcademicYearId,
                            'classroom_id' => $classroom->id,
                            'class_period_id' => $period->id,
                            'subject_id' => $subjects->random()->id,
                            'day_of_week' => $day->value,
                            'notes' => null,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];

                        if (count($records) >= self::INSERT_CHUNK_SIZE) {
                            ClassSchedule::query()->insert($records);
                            $records = [];
                        }
                    }
                }
            }
        }

        if ($records !== []) {
            ClassSchedule::query()->insert($records);
        }
    }
}
