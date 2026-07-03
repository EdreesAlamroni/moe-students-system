<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = now();

        $startYear = $today->month >= 9
            ? $today->year
            : $today->year - 1;

        $start = Carbon::create($startYear, 9, 1);
        $end = Carbon::create($startYear + 1, 6, 30);

        return [
            'name' => sprintf('%d/%d', $startYear + 1, $startYear),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => $today->betweenIncluded($start, $end),
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }
}
