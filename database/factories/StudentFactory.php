<?php

namespace Database\Factories;

use App\Enums\StudentExamEnrollmentStatus;
use App\Enums\StudentRegistrationStatus;
use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Support\Helpers\FakeDataGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        $dateOfBirth = fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d');

        $nationalId = FakeDataGenerator::libyanNationalId(
            fake(),
            $gender,
            date('Y', strtotime($dateOfBirth)),
        );

        return [
            'education_monitor_id' => EducationMonitor::factory(),
            'school_id' => School::factory(),
            'nationality_id' => Nationality::libyanId() ?? Nationality::factory(),
            'number' => fake()->unique()->numerify('#######'),
            'registration_status' => fake()->randomElement(StudentRegistrationStatus::cases()),
            'exam_enrollment_status' => fake()->randomElement(StudentExamEnrollmentStatus::cases()),

            'first_name' => fake()->firstName($gender),
            'father_name' => fake()->firstNameMale(),
            'grandfather_name' => fake()->firstNameMale(),
            'surname' => fake()->lastName('male'),

            'mother_name' => trim(sprintf(
                '%s %s %s %s',
                fake()->firstNameFemale(),
                fake()->firstNameMale(),
                fake()->firstNameMale(),
                fake()->lastName('male'),
            )),

            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'national_id' => $nationalId,
            'family_registration_number' => fake()->randomNumber(8, true),
            'passport_number' => Str::upper(fake()->regexify('[A-Z0-9]{8}')),
        ];
    }
}
