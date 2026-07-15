<?php

namespace Database\Seeders;

use App\Enums\AccommodationForm;
use App\Enums\AccommodationType;
use App\Enums\FamilyIncome;
use App\Enums\HealthLevel;
use App\Enums\StudentBehavioralProblem;
use App\Enums\StudentFamilySituationReason;
use App\Enums\StudentLivingSituation;
use App\Models\AcademicYear;
use App\Models\Nationality;
use App\Models\Student;
use App\Models\StudentPsychosocialCard;
use App\Support\Helpers\FakeDataGenerator;
use Illuminate\Database\Seeder;

class StudentPsychosocialCardSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Student|null $student */
        $student = Student::query()->find(1);

        if ($student === null) {
            return;
        }

        $student->load('enrollment');

        $academicYearId = AcademicYear::currentId();
        $enrollment = $student->enrollment;

        if ($academicYearId === null || $enrollment === null) {
            return;
        }

        $libyanNationalityId = Nationality::libyanId() ?? Nationality::query()->value('id');

        if ($libyanNationalityId === null) {
            return;
        }

        $guardianPhoneNumber = FakeDataGenerator::libyanMobile(fake());
        $representativePhoneNumber = FakeDataGenerator::libyanMobile(fake());

        StudentPsychosocialCard::query()->updateOrCreate([
            'student_id' => $student->id,
            'academic_year_id' => $academicYearId,
            'student_enrollment_id' => $enrollment->id,
        ], [
            'guardian_name' => $student->father_full_name,
            'guardian_date_of_birth' => '1978-06-12',
            'guardian_nationality_id' => $libyanNationalityId,
            'guardian_relationship' => 'أب',
            'guardian_phone_number' => $guardianPhoneNumber,
            'guardian_education_level' => 'جامعي',
            'guardian_job_title' => 'موظف حكومي',
            'guardian_work_place' => 'مصلحة حكومية - بنغازي',
            'mother_date_of_birth' => '1982-09-20',
            'mother_nationality_id' => $libyanNationalityId,
            'mother_phone_number' => FakeDataGenerator::libyanMobile(fake()),
            'mother_education_level' => 'ثانوي',
            'mother_profession' => 'ربة منزل',
            'mother_work_place' => 'منزل',
            'number_of_family_members' => 6,
            'student_family_order' => 3,
            'number_of_siblings' => 5,
            'student_living_situation' => StudentLivingSituation::WITH_PARENTS,
            'family_situation_reason' => StudentFamilySituationReason::PARENTS_SEPARATION,
            'residential_area' => 'البركة',
            'residential_street' => 'شارع جمال عبد الناصر',
            'nearest_landmark' => 'بجوار مسجد النور',
            'previous_activities' => 'نشاط كشفي، دورة في اللغة الإنجليزية',
            'talents' => 'الرسم، حفظ القرآن الكريم',
            'previous_diseases' => 'لا يوجد',
            'physical_disability_type' => 'لا يوجد',
            'vision_level' => HealthLevel::GOOD,
            'hearing_level' => HealthLevel::NORMAL,
            'family_income' => FamilyIncome::AVERAGE,
            'accommodation_type' => AccommodationType::OWNED,
            'accommodation_form' => AccommodationForm::REGULAR_HOUSE,
            'behavioral_problems' => [
                [
                    'behavior' => StudentBehavioralProblem::SHYNESS->value,
                    'has_problem' => true,
                    'notes' => 'يظهر الخجل في التجمعات العائلية',
                ],
                [
                    'behavior' => StudentBehavioralProblem::DISTRACTION->value,
                    'has_problem' => true,
                    'notes' => 'يحتاج إلى متابعة أثناء الواجبات المنزلية',
                ],
                [
                    'behavior' => StudentBehavioralProblem::LACK_OF_MOTIVATION->value,
                    'has_problem' => true,
                    'notes' => null,
                ],
            ],
            'guardian_representative_name' => 'محمد أحمد علي',
            'guardian_representative_relationship' => 'عم',
            'guardian_representative_id_card_number' => '119780612345',
            'guardian_representative_phone_number' => $representativePhoneNumber,
            'guardian_representative_work_place' => 'شركة خاصة - بنغازي',
        ]);
    }
}
