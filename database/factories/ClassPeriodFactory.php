<?php

namespace Database\Factories;

use App\Enums\SchoolAcademicPeriod;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassPeriod>
 */
class ClassPeriodFactory extends Factory
{
    private const BASE_HOUR = 7;

    private const PERIOD_DURATION_MINUTES = 45;

    private static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = ++self::$sequence;
        $startHour = self::BASE_HOUR + $order;

        return [
            'academic_year_id' => AcademicYear::currentId(),
            'academic_period' => SchoolAcademicPeriod::MORNING,
            'name' => sprintf('الحصة %d', $order),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:%02d', $startHour, self::PERIOD_DURATION_MINUTES),
            'order' => $order,
            'is_break' => false,
        ];
    }

    public function asBreak(): static
    {
        return $this->state([
            'name' => 'استراحة',
            'is_break' => true,
        ]);
    }
}
