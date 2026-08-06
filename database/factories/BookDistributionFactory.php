<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookDistribution>
 */
class BookDistributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::currentId() ?? AcademicYear::factory(),
            'school_period_id' => SchoolPeriod::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'warehouse_id' => function (array $attributes) {
                $educationMonitorId = SchoolPeriod::query()
                    ->where('id', '=', $attributes['school_period_id'])
                    ->value('education_monitor_id');

                return EducationMonitor::query()
                    ->where('id', '=', $educationMonitorId)
                    ->value('warehouse_id');
            },
            'distributed_at' => now(),
        ];
    }
}
