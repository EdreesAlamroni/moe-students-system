<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Support\AcademicYearCalendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    private const DEFAULT_START_YEAR = 2026;

    private static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return AcademicYearCalendar::attributesForStartYear(
            self::DEFAULT_START_YEAR + self::$sequence++,
        );
    }

    public function forStartYear(int $startYear): static
    {
        return $this->state(AcademicYearCalendar::attributesForStartYear($startYear));
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }
}
