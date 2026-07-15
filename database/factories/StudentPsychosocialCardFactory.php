<?php

namespace Database\Factories;

use App\Enums\AccommodationForm;
use App\Enums\AccommodationType;
use App\Enums\FamilyIncome;
use App\Enums\HealthLevel;
use App\Enums\StudentBehavioralProblem;
use App\Enums\StudentFamilySituationReason;
use App\Enums\StudentLivingSituation;
use App\Models\AcademicYear;
use App\Models\Nationality;
use App\Models\StudentEnrollment;
use App\Models\StudentPsychosocialCard;
use App\Support\Helpers\FakeDataGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentPsychosocialCard>
 */
class StudentPsychosocialCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $livingSituation = fake()->randomElement(StudentLivingSituation::cases());
        $numberOfSiblings = fake()->numberBetween(0, 7);
        $studentFamilyOrder = fake()->numberBetween(1, $numberOfSiblings + 1);
        $numberOfFamilyMembers = $numberOfSiblings + 1 + fake()->numberBetween(1, 3);
        $hasRepresentative = fake()->boolean(40);

        $educationLevels = ['أمي', 'ابتدائي', 'إعدادي', 'ثانوي', 'جامعي', 'فوق الجامعي'];
        $relationships = ['أب', 'أم', 'جد', 'جدة', 'عم', 'خال', 'وصي'];

        return [
            'student_enrollment_id' => StudentEnrollment::factory()->state([
                'academic_year_id' => AcademicYear::currentId() ?? AcademicYear::factory(),
            ]),
            'academic_year_id' => fn (array $attributes) => StudentEnrollment::query()
                ->whereKey($attributes['student_enrollment_id'])
                ->value('academic_year_id'),
            'student_id' => fn (array $attributes) => StudentEnrollment::query()
                ->whereKey($attributes['student_enrollment_id'])
                ->value('student_id'),

            'guardian_name' => trim(sprintf(
                '%s %s %s %s',
                fake()->firstNameMale(),
                fake()->firstNameMale(),
                fake()->firstNameMale(),
                fake()->lastName('male'),
            )),
            'guardian_date_of_birth' => fake()->dateTimeBetween('-70 years', '-30 years')->format('Y-m-d'),
            'guardian_nationality_id' => Nationality::libyanId() ?? Nationality::factory(),
            'guardian_relationship' => fake()->randomElement($relationships),
            'guardian_phone_number' => FakeDataGenerator::libyanMobile(fake()),
            'guardian_education_level' => fake()->randomElement($educationLevels),
            'guardian_job_title' => fake()->jobTitle(),
            'guardian_work_place' => fake()->company(),

            'mother_date_of_birth' => fake()->dateTimeBetween('-65 years', '-25 years')->format('Y-m-d'),
            'mother_nationality_id' => fn (array $attributes) => $attributes['guardian_nationality_id'],
            'mother_phone_number' => FakeDataGenerator::libyanMobile(fake()),
            'mother_education_level' => fake()->randomElement($educationLevels),
            'mother_profession' => fake()->jobTitle(),
            'mother_work_place' => fake()->company(),

            'number_of_family_members' => $numberOfFamilyMembers,
            'student_family_order' => $studentFamilyOrder,
            'number_of_siblings' => $numberOfSiblings,

            'student_living_situation' => $livingSituation,
            'family_situation_reason' => $this->familySituationReasonFor($livingSituation),
            'residential_area' => fake()->city(),
            'residential_street' => fake()->streetName(),
            'nearest_landmark' => fake()->optional(0.7)->streetName(),
            'previous_activities' => fake()->optional(0.5)->sentence(),
            'talents' => fake()->optional(0.5)->sentence(),

            'previous_diseases' => fake()->optional(0.25)->sentence(),
            'physical_disability_type' => fake()->optional(0.1)->sentence(),
            'vision_level' => fake()->randomElement(HealthLevel::cases()),
            'hearing_level' => fake()->randomElement(HealthLevel::cases()),

            'family_income' => fake()->randomElement(FamilyIncome::cases()),
            'accommodation_type' => fake()->randomElement(AccommodationType::cases()),
            'accommodation_form' => fake()->randomElement(AccommodationForm::cases()),

            'behavioral_problems' => fake()->boolean(30)
                ? fake()->randomElements(
                    StudentBehavioralProblem::values(),
                    fake()->numberBetween(1, 3),
                )
                : null,

            'guardian_representative_name' => $hasRepresentative ? fake()->name('male') : null,
            'guardian_representative_relationship' => $hasRepresentative
                ? fake()->randomElement($relationships)
                : null,
            'guardian_representative_id_card_number' => $hasRepresentative
                ? FakeDataGenerator::libyanNationalId(fake(), 'male')
                : null,
            'guardian_representative_phone_number' => $hasRepresentative
                ? FakeDataGenerator::libyanMobile(fake())
                : null,
            'guardian_representative_work_place' => $hasRepresentative ? fake()->company() : null,
        ];
    }

    private function familySituationReasonFor(StudentLivingSituation $livingSituation): ?StudentFamilySituationReason
    {
        return match ($livingSituation) {
            StudentLivingSituation::WITH_PARENTS => null,
            StudentLivingSituation::WITH_FATHER => fake()->randomElement([
                StudentFamilySituationReason::MOTHER_DEATH,
                StudentFamilySituationReason::PARENTS_SEPARATION,
            ]),
            StudentLivingSituation::WITH_MOTHER => fake()->randomElement([
                StudentFamilySituationReason::FATHER_DEATH,
                StudentFamilySituationReason::PARENTS_SEPARATION,
            ]),
            default => fake()->randomElement(StudentFamilySituationReason::cases()),
        };
    }
}
