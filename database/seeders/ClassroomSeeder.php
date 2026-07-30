<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevelSchool;
use App\Models\School;
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

        $schools = School::query()->get(['id']);

        if ($schools->isEmpty()) {
            return;
        }

        $gradeLevelsBySchool = GradeLevelSchool::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->get(['school_id', 'grade_level_id'])
            ->groupBy('school_id');

        foreach ($schools as $school) {
            $gradeLevels = $gradeLevelsBySchool->get($school->id, collect([]));

            if ($gradeLevels->isEmpty()) {
                continue;
            }

            foreach ($gradeLevels as $gradeLevelSchool) {
                foreach (self::CLASSROOM_NAMES as $name) {
                    Classroom::query()->firstOrCreate([
                        'academic_year_id' => $currentAcademicYearId,
                        'school_id' => $school->id,
                        'grade_level_id' => $gradeLevelSchool->grade_level_id,
                        'name' => $name,
                    ], [
                        'capacity' => self::DEFAULT_CAPACITY,
                    ]);
                }
            }
        }
    }
}
