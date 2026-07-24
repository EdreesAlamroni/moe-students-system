<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookDistributionItem>
 */
class BookDistributionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_distribution_id' => BookDistribution::factory(),
            'academic_year_id' => AcademicYear::currentId() ?? AcademicYear::factory(),
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
        ];
    }
}
