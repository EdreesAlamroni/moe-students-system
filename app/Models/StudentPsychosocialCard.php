<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\AccommodationForm;
use App\Enums\AccommodationType;
use App\Enums\FamilyIncome;
use App\Enums\HealthLevel;
use App\Enums\StudentFamilySituationReason;
use App\Enums\StudentLivingSituation;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $academic_year_id
 * @property int $student_id
 * @property int $student_enrollment_id
 * @property string|null $guardian_name
 * @property Carbon|null $guardian_date_of_birth
 * @property int|null $guardian_nationality_id
 * @property string|null $guardian_relationship
 * @property string|null $guardian_phone_number
 * @property string|null $guardian_education_level
 * @property string|null $guardian_job_title
 * @property string|null $guardian_work_place
 * @property Carbon|null $mother_date_of_birth
 * @property int|null $mother_nationality_id
 * @property string|null $mother_phone_number
 * @property string|null $mother_education_level
 * @property string|null $mother_profession
 * @property string|null $mother_work_place
 * @property int|null $number_of_family_members
 * @property int|null $student_family_order
 * @property int|null $number_of_siblings
 * @property StudentLivingSituation|null $student_living_situation
 * @property StudentFamilySituationReason|null $family_situation_reason
 * @property string|null $residential_area
 * @property string|null $residential_street
 * @property string|null $nearest_landmark
 * @property string|null $previous_activities
 * @property string|null $talents
 * @property string|null $previous_diseases
 * @property string|null $physical_disability_type
 * @property HealthLevel|null $vision_level
 * @property HealthLevel|null $hearing_level
 * @property FamilyIncome|null $family_income
 * @property AccommodationType|null $accommodation_type
 * @property AccommodationForm|null $accommodation_form
 * @property array|null $behavioral_problems
 * @property string|null $guardian_representative_name
 * @property string|null $guardian_representative_relationship
 * @property string|null $guardian_representative_id_card_number
 * @property string|null $guardian_representative_phone_number
 * @property string|null $guardian_representative_work_place
 * @property-read AcademicYear $academicYear
 * @property-read Student $student
 * @property-read StudentEnrollment $enrollment
 * @property-read Nationality|null $guardianNationality
 * @property-read Nationality|null $motherNationality
 */
#[Guarded(['id'])]
class StudentPsychosocialCard extends Model
{
    /** @use HasFactory<\Database\Factories\StudentPsychosocialCardFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'student_id' => 'integer',
            'student_enrollment_id' => 'integer',
            'guardian_date_of_birth' => 'date',
            'guardian_nationality_id' => 'integer',
            'mother_date_of_birth' => 'date',
            'mother_nationality_id' => 'integer',
            'number_of_family_members' => 'integer',
            'student_family_order' => 'integer',
            'number_of_siblings' => 'integer',
            'student_living_situation' => StudentLivingSituation::class,
            'family_situation_reason' => StudentFamilySituationReason::class,
            'vision_level' => HealthLevel::class,
            'hearing_level' => HealthLevel::class,
            'family_income' => FamilyIncome::class,
            'accommodation_type' => AccommodationType::class,
            'accommodation_form' => AccommodationForm::class,
            'behavioral_problems' => 'array',
        ];
    }

    /*
     * Start: Relations
     */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function guardianNationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'guardian_nationality_id');
    }

    public function motherNationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'mother_nationality_id');
    }

    /*
     * End: Relations
     */

}
