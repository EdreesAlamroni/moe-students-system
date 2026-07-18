<?php

namespace Database\Factories;

use App\Enums\GradeLevelEnum;
use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use OverflowException;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $existingCodes = GradeLevel::query()->pluck('code')->all();

        foreach (GradeLevelEnum::cases() as $grade) {
            if (in_array($grade->value, $existingCodes, true)) {
                continue;
            }

            return [
                'code' => $grade->value,
                'name' => $grade->label(),
                'educational_stage' => $grade->stage(),
                'order' => $grade->order(),
            ];
        }

        throw new OverflowException('All grade level codes have already been created.');
    }
}
