<?php

namespace Database\Factories;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Support\Helpers\FakeDataGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationServicesOffice>
 */
class EducationServicesOfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phoneNumber = FakeDataGenerator::libyanMobile(fake());

        return [
            'education_monitor_id' => EducationMonitor::factory(),
            'name' => fake()->company(),
            'phone_number' => $phoneNumber,
            'whatsapp_phone_number' => $phoneNumber,
            'address' => fake()->address(),
            'latitude' => fake()->latitude(19.5, 33.2),
            'longitude' => fake()->longitude(9.3, 25.2),
        ];
    }
}
