<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\SchoolPeriod;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    private const DEFAULT_CAPACITY = 30;

    /**
     * @var list<string>
     */
    private const CLASSROOM_NAMES = ['1', '2', '3'];

    public function run(): void
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if ($currentAcademicYearId === null) {
            return;
        }

        $schoolPeriods = SchoolPeriod::query()->get(['id']);

        if ($schoolPeriods->isEmpty()) {
            return;
        }

        $gradeLevelsBySchoolPeriod = GradeLevelSchoolPeriod::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->get(['school_period_id', 'grade_level_id'])
            ->groupBy('school_period_id');

        foreach ($schoolPeriods as $schoolPeriod) {
            $gradeLevels = $gradeLevelsBySchoolPeriod->get($schoolPeriod->id, collect([]));

            if ($gradeLevels->isEmpty()) {
                continue;
            }

            foreach ($gradeLevels as $gradeLevelSchoolPeriod) {
                foreach (self::CLASSROOM_NAMES as $name) {
                    Classroom::query()->firstOrCreate([
                        'academic_year_id' => $currentAcademicYearId,
                        'school_period_id' => $schoolPeriod->id,
                        'grade_level_id' => $gradeLevelSchoolPeriod->grade_level_id,
                        'name' => $name,
                    ], [
                        'capacity' => self::DEFAULT_CAPACITY,
                    ]);
                }
            }
        }
    }
}
