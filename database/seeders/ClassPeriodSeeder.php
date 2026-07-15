<?php

namespace Database\Seeders;

use App\Enums\SchoolAcademicPeriod;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use Illuminate\Database\Seeder;

class ClassPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $academicYearId = AcademicYear::currentId();

        if ($academicYearId === null) {
            return;
        }

        ClassPeriod::factory()->createMany([
            ...$this->periodsFor(
                SchoolAcademicPeriod::MORNING,
                $academicYearId,
                $this->morningSchedule()
            ),
            ...$this->periodsFor(
                SchoolAcademicPeriod::EVENING,
                $academicYearId,
                $this->eveningSchedule()
            ),
        ]);
    }

    private function periodsFor(SchoolAcademicPeriod $academicPeriod, int $academicYearId, array $schedule): array
    {
        $periods = [];

        foreach ($schedule as $index => $period) {
            $periods[] = [
                'academic_year_id' => $academicYearId,
                'academic_period' => $academicPeriod,
                'name' => $period['name'],
                'start_time' => $period['start_time'],
                'end_time' => $period['end_time'],
                'order' => $index + 1,
                'is_break' => $period['is_break'] ?? false,
            ];
        }

        return $periods;
    }

    private function morningSchedule(): array
    {
        return [
            [
                'name' => 'الحصة الأولى',
                'start_time' => '08:15',
                'end_time' => '08:55',
            ],
            [
                'name' => 'الحصة الثانية',
                'start_time' => '08:55',
                'end_time' => '09:35',
            ],
            [
                'name' => 'الحصة الثالثة',
                'start_time' => '09:35',
                'end_time' => '10:15',
            ],
            [
                'name' => 'الحصة الرابعة',
                'start_time' => '10:15',
                'end_time' => '10:55',
            ],
            [
                'name' => 'الاستراحة',
                'start_time' => '10:55',
                'end_time' => '11:15',
                'is_break' => true,
            ],
            [
                'name' => 'الحصة الخامسة',
                'start_time' => '11:15',
                'end_time' => '11:55',
            ],
            [
                'name' => 'الحصة السادسة',
                'start_time' => '11:55',
                'end_time' => '12:35',
            ],
            [
                'name' => 'الحصة السابعة',
                'start_time' => '12:35',
                'end_time' => '13:15',
            ],
        ];
    }

    private function eveningSchedule(): array
    {
        return [
            [
                'name' => 'الحصة الأولى',
                'start_time' => '01:30',
                'end_time' => '02:10',
            ],
            [
                'name' => 'الحصة الثانية',
                'start_time' => '02:10',
                'end_time' => '02:50',
            ],
            [
                'name' => 'الحصة الثالثة',
                'start_time' => '02:50',
                'end_time' => '03:30',
            ],
            [
                'name' => 'الاستراحة',
                'start_time' => '03:30',
                'end_time' => '03:45',
                'is_break' => true,
            ],
            [
                'name' => 'الحصة الرابعة',
                'start_time' => '03:45',
                'end_time' => '04:20',
            ],
            [
                'name' => 'الحصة الخامسة',
                'start_time' => '04:20',
                'end_time' => '05:00',
            ],
            [
                'name' => 'الحصة السادسة',
                'start_time' => '05:00',
                'end_time' => '05:45',
            ],
        ];
    }
}
