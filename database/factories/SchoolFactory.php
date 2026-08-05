<?php

namespace Database\Factories;

use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolType;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'education_monitor_id' => EducationMonitor::factory(),
            'education_services_office_id' => function (array $attributes) {
                return EducationServicesOffice::where('education_monitor_id', '=', $attributes['education_monitor_id'])->value('id');
            },
            'type' => fake()->randomElement(SchoolType::cases()),
            'name' => fake()->unique()->company(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (School $school) {
            if ($school->isPrivate()) {
                $school->educational_company_name = fake()->company();
                $school->branch_type = fake()->randomElement(SchoolBranchType::cases());
                $school->building_type = fake()->randomElement(SchoolBuildingType::cases());
            }
        });
    }
}
