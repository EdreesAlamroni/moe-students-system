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
        return [
            'academic_year_id' => AcademicYear::currentId(),
            'academic_period' => SchoolAcademicPeriod::MORNING,
            'order' => fn () => ++self::$sequence,
            'name' => fn (array $attributes) => sprintf('الحصة %d', $attributes['order']),
            'start_time' => fn (array $attributes) => sprintf(
                '%02d:00',
                self::startHourForOrder($attributes['order']),
            ),
            'end_time' => fn (array $attributes) => sprintf(
                '%02d:%02d',
                self::startHourForOrder($attributes['order']),
                self::PERIOD_DURATION_MINUTES,
            ),
            'is_break' => false,
        ];
    }

    private static function startHourForOrder(int $order): int
    {
        $maxPeriods = 24 - self::BASE_HOUR;

        return self::BASE_HOUR + (($order - 1) % $maxPeriods);
    }

    public function asBreak(): static
    {
        return $this->state([
            'name' => 'استراحة',
            'is_break' => true,
        ]);
    }
}
