<?php

namespace Database\Seeders;

use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolType;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchool;
use App\Models\School;
use App\Models\SchoolEducationalStage;
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

        $academicYearId = AcademicYear::currentId();

        if ($academicYearId === null) {
            return;
        }

        $attributes = School::factory()->raw([
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => null,
            'name' => 'مدرسة تجريبية',
            'type' => SchoolType::PUBLIC,
        ]);

        $school = School::query()->firstOrCreate(
            [
                'education_monitor_id' => $monitor->id,
                'name' => 'مدرسة تجريبية',
            ],
            [
                'type' => $attributes['type'],
                'academic_period' => $attributes['academic_period'],
                'students_gender' => $attributes['students_gender'],
                'phone_number' => $attributes['phone_number'],
                'whatsapp_phone_number' => $attributes['whatsapp_phone_number'],
            ],
        );

        $stages = $this->educationalStages();

        foreach ($stages as $stage) {
            SchoolEducationalStage::query()->updateOrCreate([
                'academic_year_id' => $academicYearId,
                'school_id' => $school->id,
                'stage' => $stage,
            ], []);
        }

        $gradeLevels = GradeLevel::query()
            ->whereIn('educational_stage', $stages)
            ->get();

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevelSchool::query()->updateOrCreate([
                'grade_level_id' => $gradeLevel->id,
                'school_id' => $school->id,
                'academic_year_id' => $academicYearId,
            ], []);
        }
    }

    protected function educationalStages(): array
    {
        return [
            SchoolEducationalStageEnum::PRIMARY_EDUCATION,
            SchoolEducationalStageEnum::SECONDARY_EDUCATION,
        ];
    }
}
