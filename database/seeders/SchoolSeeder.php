<?php

namespace Database\Seeders;

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $monitor = EducationMonitor::query()
            ->whereHas('municipal', function ($query): void {
                $query->where('name', '=', 'بنغازي');
            })
            ->first();

        if ($monitor === null) {
            return;
        }

        $currentAcademicYearId = AcademicYear::currentId();

        if ($currentAcademicYearId === null) {
            return;
        }

        $officeId = EducationServicesOffice::query()
            ->where('education_monitor_id', '=', $monitor->id)
            ->value('id') ?? null;

        $attributes = School::factory()->raw([
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => $officeId,
            'name' => 'مدرسة تجريبية',
            'type' => SchoolType::PUBLIC,
        ]);

        $school = School::query()->firstOrCreate(
            [
                'education_monitor_id' => $monitor->id,
                'education_services_office_id' => $officeId,
                'name' => 'مدرسة تجريبية',
            ],
            [
                'type' => $attributes['type'],
            ],
        );

        $schoolPeriod = SchoolPeriod::query()->firstOrCreate(
            [
                'school_id' => $school->id,
                'academic_period' => collect(SchoolAcademicPeriod::values())->random(),
            ],
            [
                'students_gender' => collect(SchoolStudentsGender::values())->random(),

            ],
        );

        $stages = $this->educationalStages();

        foreach ($stages as $stage) {
            SchoolEducationalStage::query()->updateOrCreate([
                'academic_year_id' => $currentAcademicYearId,
                'school_period_id' => $schoolPeriod->id,
                'stage' => $stage,
            ], []);
        }

        $gradeLevels = GradeLevel::query()
            ->whereIn('educational_stage', $stages)
            ->get();

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevelSchoolPeriod::query()->updateOrCreate([
                'grade_level_id' => $gradeLevel->id,
                'school_period_id' => $schoolPeriod->id,
                'academic_year_id' => $currentAcademicYearId,
            ], []);
        }
    }

    protected function educationalStages(): array
    {
        return [
            SchoolEducationalStageEnum::KINDERGARTEN,
            SchoolEducationalStageEnum::PRIMARY_EDUCATION,
            SchoolEducationalStageEnum::SECONDARY_EDUCATION,
        ];
    }
}
