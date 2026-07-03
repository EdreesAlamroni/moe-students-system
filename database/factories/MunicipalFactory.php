<?php

namespace Database\Factories;

use App\Models\Municipal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Municipal>
 */
class MunicipalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
        ];
    }
}
