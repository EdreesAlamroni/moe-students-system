<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Enums\SchoolAcademicPeriod;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::query()->get();

        if ($schools->isEmpty()) {
            return;
        }

        foreach ($schools as $school) {
            $academicYearId = AcademicYear::currentId();

            $this->createPeriodsForSchool($school, $academicYearId);
            $this->createSchedulesForSchool($school, $academicYearId);
        }
    }

    private function createPeriodsForSchool(School $school, ?int $academicYearId): void
    {
        if (is_null($academicYearId)) {
            return;
        }

        $existingCount = ClassPeriod::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $periods = [
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة الأولى', 'start_time' => '08:15', 'end_time' => '08:55', 'order' => 1, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة الثانية', 'start_time' => '08:55', 'end_time' => '09:35', 'order' => 2, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة الثالثة', 'start_time' => '09:35', 'end_time' => '10:15', 'order' => 3, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة الرابعة', 'start_time' => '10:15', 'end_time' => '10:55', 'order' => 4, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الاستراحة', 'start_time' => '10:55', 'end_time' => '11:15', 'order' => 5, 'is_break' => true],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة الخامسة', 'start_time' => '11:15', 'end_time' => '11:55', 'order' => 6, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة السادسة', 'start_time' => '11:55', 'end_time' => '12:35', 'order' => 7, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::MORNING, 'name' => 'الحصة السابعة', 'start_time' => '12:35', 'end_time' => '13:15', 'order' => 8, 'is_break' => false],

            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة الأولى', 'start_time' => '01:30', 'end_time' => '02:10', 'order' => 1, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة الثانية', 'start_time' => '02:10', 'end_time' => '02:50', 'order' => 2, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة الثالثة', 'start_time' => '02:50', 'end_time' => '03:30', 'order' => 3, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الاستراحة', 'start_time' => '03:30', 'end_time' => '03:45', 'order' => 4, 'is_break' => true],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة الرابعة', 'start_time' => '03:45', 'end_time' => '04:20', 'order' => 5, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة الخامسة', 'start_time' => '04:20', 'end_time' => '05:00', 'order' => 6, 'is_break' => false],
            ['academic_period' => SchoolAcademicPeriod::EVENING, 'name' => 'الحصة السادسة', 'start_time' => '05:00', 'end_time' => '05:45', 'order' => 7, 'is_break' => false],
        ];

        foreach ($periods as $periodData) {
            ClassPeriod::create([
                'academic_year_id' => $academicYearId,
                ...$periodData,
            ]);
        }
    }

    private function createSchedulesForSchool(School $school, ?int $academicYearId): void
    {
        if (is_null($academicYearId)) {
            return;
        }

        $classrooms = Classroom::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('school_id', '=', $school->id)
            // ->limit(2)
            ->get();

        if ($classrooms->isEmpty()) {
            return;
        }

        $periods = ClassPeriod::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('academic_period', '=', $school->academic_period)
            ->where('is_break', '=', false)
            ->ordered()
            ->get();

        $days = DayOfWeek::schoolDays();

        foreach ($classrooms as $classroom) {
            $existingCount = ClassSchedule::query()
                ->where('classroom_id', '=', $classroom->id)
                ->count();

            if ($existingCount > 0) {
                continue;
            }

            $subjects = Subject::query()
                ->where('grade_level_id', '=', $classroom->grade_level_id)
                // ->limit(10)
                ->get();

            if ($subjects->isEmpty()) {
                continue;
            }

            foreach ($periods as $period) {
                foreach ($days as $day) {
                    $subject = $subjects->random();

                    ClassSchedule::create([
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYearId,
                        'classroom_id' => $classroom->id,
                        'class_period_id' => $period->id,
                        'subject_id' => $subject->id,
                        'day_of_week' => $day,
                    ]);
                }
            }
        }
    }
}
