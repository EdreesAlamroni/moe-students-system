<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Warehouse;
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
            'education_monitor_id' => EducationMonitor::factory(),
            'school_id' => School::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'warehouse_id' => Warehouse::factory(),
            'distributed_at' => now(),
        ];
    }
}
