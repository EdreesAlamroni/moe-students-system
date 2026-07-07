<?php

namespace Database\Factories;

use App\Enums\GradeLevelEnum;
use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        static $pool = null;
        static $index = 0;

        if ($pool === null) {
            $pool = GradeLevelEnum::cases();
            shuffle($pool); // randomize once per process
            $index = 0;
        }

        if ($index >= count($pool)) {
            shuffle($pool);
            $index = 0;
        }

        /** @var GradeLevelEnum $grade */
        $grade = $pool[$index];
        $index++;

        return [
            'code' => $grade->value,
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ];
    }
}
