<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassScheduleSeeder extends Seeder
{
    private const INSERT_CHUNK_SIZE = 500;

    public function run(): void
    {
        $academicYearId = AcademicYear::currentId();

        if ($academicYearId === null) {
            return;
        }

        if (! ClassPeriod::query()->where('academic_year_id', '=', $academicYearId)->exists()) {
            $this->call(ClassPeriodSeeder::class);
        }

        $schools = School::query()->get(['id', 'academic_period']);

        if ($schools->isEmpty()) {
            return;
        }

        $classroomsBySchool = Classroom::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->get(['id', 'school_id', 'grade_level_id'])
            ->groupBy('school_id');

        if ($classroomsBySchool->isEmpty()) {
            return;
        }

        $periodsByAcademicPeriod = ClassPeriod::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('is_break', '=', false)
            ->ordered()
            ->get(['id', 'academic_period'])
            ->groupBy(fn (ClassPeriod $period): string => $period->academic_period->value);

        $subjectsByGradeLevel = Subject::query()
            ->get(['id', 'grade_level_id'])
            ->groupBy('grade_level_id');

        $seededClassroomIds = ClassSchedule::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->distinct()
            ->pluck('classroom_id');

        $days = DayOfWeek::schoolDays();
        $timestamp = now();
        $records = [];

        foreach ($schools as $school) {
            $classrooms = $classroomsBySchool->get($school->id, collect());

            if ($classrooms->isEmpty()) {
                continue;
            }

            $periods = $periodsByAcademicPeriod->get($school->academic_period->value, collect());

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
                            'school_id' => $school->id,
                            'academic_year_id' => $academicYearId,
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
