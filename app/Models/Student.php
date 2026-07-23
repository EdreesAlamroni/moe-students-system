<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\Gender;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\StudentExamEnrollmentStatus;
use App\Enums\StudentRegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $education_monitor_id
 * @property int|null $school_id
 * @property int $nationality_id
 * @property string $number
 * @property StudentRegistrationStatus $registration_status
 * @property StudentExamEnrollmentStatus $exam_enrollment_status
 * @property string $first_name
 * @property string $father_name
 * @property string $grandfather_name
 * @property string $surname
 * @property string $mother_name
 * @property Gender $gender
 * @property Carbon $date_of_birth
 * @property string|null $national_id
 * @property string|null $family_registration_number
 * @property string|null $passport_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $full_name
 * @property-read string $father_full_name
 * @property-read bool $is_libyan
 * @property-read bool $already_distributed
 * @property-read EducationMonitor|null $monitor
 * @property-read School|null $school
 * @property-read Nationality $nationality
 * @property-read Collection<int, StudentEnrollment> $enrollments
 * @property-read StudentEnrollment|null $enrollment
 * @property-read Collection<int, StudentTransfer> $transfers
 * @property-read StudentTransfer|null $transfer
 * @property-read Collection<int, StudentPsychosocialCard> $psychosocialCards
 * @property-read StudentPsychosocialCard|null $psychosocialCard
 */
#[Guarded(['id'])]
class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'education_monitor_id' => 'integer',
            'school_id' => 'integer',
            'nationality_id' => 'integer',
            'registration_status' => StudentRegistrationStatus::class,
            'exam_enrollment_status' => StudentExamEnrollmentStatus::class,
            'gender' => Gender::class,
            'date_of_birth' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $student) {
            $id = (string) $student->id;
            $number = Str::padLeft($id, 7, '0');
            $student->updateQuietly([
                'number' => $number,
                'exam_enrollment_status' => StudentExamEnrollmentStatus::REGISTERED,
            ]);
        });
    }

    /*
     * Start: Accessors & Mutators
     */

    public function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            $fullName = sprintf(
                '%s %s %s %s',
                $this->first_name,
                $this->father_name,
                $this->grandfather_name,
                $this->surname,
            );

            return trim($fullName);
        });
    }

    public function fatherFullName(): Attribute
    {
        return Attribute::get(function (): string {
            $fullName = sprintf(
                '%s %s %s',
                $this->father_name,
                $this->grandfather_name,
                $this->surname,
            );

            return trim($fullName);
        });
    }

    public function isLibyan(): Attribute
    {
        return Attribute::get(function (): bool {
            return $this->nationality->isLibyan();
        });
    }

    /*
     * End: Accessors & Mutators
     */

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentEducationMonitor(Builder $query): Builder
    {
        $id = auth('education_monitor')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('education_monitor_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentEducationServicesOffice(Builder $query): Builder
    {
        $id = auth('education_services_office')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->whereHas('school', function (Builder $query) use ($id): void {
            $query->where('education_services_office_id', '=', $id);
        });
    }

    #[Scope]
    protected function forCurrentSchool(Builder $query): Builder
    {
        $id = auth('school')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where($query->getModel()->qualifyColumn('school_id'), '=', $id);
    }

    #[Scope]
    protected function byFullName(Builder $query, string $name): Builder
    {
        $terms = preg_split('/\s+/', trim($name));

        return $query->where(function (Builder $query) use ($terms) {
            foreach ($terms as $term) {
                $query->whereFullText(
                    ['first_name', 'father_name', 'grandfather_name', 'surname'],
                    "+{$term}*",
                    ['mode' => 'boolean']
                );
            }
        });
    }

    #[Scope]
    protected function orderByFullName(Builder $query): Builder
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
            return $query
                ->orderBy('first_name')
                ->orderBy('father_name')
                ->orderBy('grandfather_name')
                ->orderBy('surname');
        }

        return $query->orderByRaw('
            first_name COLLATE utf8mb4_unicode_ci ASC,
            father_name COLLATE utf8mb4_unicode_ci ASC,
            grandfather_name COLLATE utf8mb4_unicode_ci ASC,
            surname COLLATE utf8mb4_unicode_ci ASC
        ');
    }

    #[Scope]
    protected function assignedToEducationMonitor(Builder $query): Builder
    {
        return $query->whereNotNull($query->qualifyColumn('education_monitor_id'));
    }

    #[Scope]
    protected function unassignedToEducationMonitor(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('education_monitor_id'));
    }

    #[Scope]
    protected function unassignedToSchool(Builder $query): Builder
    {
        return $query->whereNull($query->qualifyColumn('school_id'));
    }

    #[Scope]
    protected function unenrolledFromGradeLevel(Builder $query): Builder
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereDoesntHave('enrollments', function (Builder $query) use ($currentAcademicYearId): void {
            $query->where('academic_year_id', '=', $currentAcademicYearId);
        });
    }

    #[Scope]
    protected function unenrolledFromClassroom(Builder $query): Builder
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('enrollments', function (Builder $query) use ($currentAcademicYearId): void {
            $query
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->whereNotNull('grade_level_id')
                ->whereNull('classroom_id');
        });
    }

    #[Scope]
    protected function eligibleForSchoolTransfer(Builder $query, School $school): Builder
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query
            ->whereNull("{$table}.school_id")
            ->where(function (Builder $query) use ($school, $table): void {
                $query
                    ->whereNull("{$table}.education_monitor_id")
                    ->orWhere("{$table}.education_monitor_id", '=', $school->education_monitor_id);
            })
            ->whereExists(function (QueryBuilder $subquery) use ($school, $currentAcademicYearId, $table): void {
                $subquery
                    ->selectRaw('1')
                    ->from('student_enrollments')
                    ->join('grade_level_school', function (JoinClause $join) use ($school, $currentAcademicYearId): void {
                        $join
                            ->on('grade_level_school.grade_level_id', '=', 'student_enrollments.grade_level_id')
                            ->where('grade_level_school.school_id', '=', $school->id)
                            ->where('grade_level_school.academic_year_id', '=', $currentAcademicYearId);
                    })
                    ->whereColumn('student_enrollments.student_id', "{$table}.id")
                    ->where('student_enrollments.academic_year_id', '=', $currentAcademicYearId)
                    ->whereNull('student_enrollments.deleted_at');
            });
    }

    #[Scope]
    protected function awaitingSchoolTransfer(Builder $query): Builder
    {
        return $query->whereHas('transfer', function (Builder $query): void {
            $query
                ->whereNotNull([
                    'left_academic_year_id',
                    'from_school_id',
                    'left_school_at',
                ])
                ->whereNull([
                    'joined_academic_year_id',
                    'to_school_id',
                    'joined_school_at',
                ]);
        });
    }

    #[Scope]
    protected function withCurrentGradeLevel(Builder $query): Builder
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query
            ->join('student_enrollments', function (JoinClause $join) use ($currentAcademicYearId, $table): void {
                $join->on('student_enrollments.student_id', '=', "{$table}.id")
                    ->where('student_enrollments.academic_year_id', '=', $currentAcademicYearId)
                    ->whereNull('student_enrollments.deleted_at');
            })
            ->join('grade_levels', function (JoinClause $join): void {
                $join->on('grade_levels.id', '=', 'student_enrollments.grade_level_id')
                    ->whereNull('grade_levels.deleted_at');
            });
    }

    #[Scope]
    protected function orderByGradeLevel(Builder $query, string $direction = 'asc'): Builder
    {
        $stages = SchoolEducationalStageEnum::orderedValues();

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
            return $query
                ->orderBy('grade_levels.educational_stage')
                ->orderBy('grade_levels.order', $direction);
        }

        return $query
            ->orderByRaw('FIELD(grade_levels.educational_stage, ?, ?, ?)', $stages)
            ->orderBy('grade_levels.order', $direction);
    }

    #[Scope]
    protected function withCurrentClassroom(Builder $query): Builder
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query
            ->join('student_enrollments', function (JoinClause $join) use ($currentAcademicYearId, $table): void {
                $join->on('student_enrollments.student_id', '=', "{$table}.id")
                    ->where('student_enrollments.academic_year_id', '=', $currentAcademicYearId)
                    ->whereNotNull('student_enrollments.classroom_id')
                    ->whereNull('student_enrollments.deleted_at');
            })
            ->join('grade_levels', function (JoinClause $join): void {
                $join->on('grade_levels.id', '=', 'student_enrollments.grade_level_id')
                    ->whereNull('grade_levels.deleted_at');
            })
            ->join('classrooms', function (JoinClause $join): void {
                $join->on('classrooms.id', '=', 'student_enrollments.classroom_id')
                    ->whereNull('classrooms.deleted_at');
            });
    }

    #[Scope]
    protected function orderByClassroom(Builder $query, string $direction = 'asc'): Builder
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'sqlite') {
            return $query->orderBy('classrooms.name', $direction);
        }

        return $query->orderByRaw("classrooms.name COLLATE utf8mb4_unicode_ci {$direction}");
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get all enrollments associated with the student across all academic years.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get the enrollment associated with the student for the current academic year.
     */
    public function enrollment(): HasOne
    {
        return $this
            ->hasOne(StudentEnrollment::class)
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    /**
     * Get all transfers associated with the student across all academic years.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class);
    }

    /**
     * Get the transfer associated with the student for the current academic year.
     */
    public function transfer(): HasOne
    {
        return $this->hasOne(StudentTransfer::class)->latestOfMany();
    }

    /**
     * Get all psychosocial cards associated with the student across all academic years.
     */
    public function psychosocialCards(): HasMany
    {
        return $this->hasMany(StudentPsychosocialCard::class);
    }

    /**
     * Get the psychosocial card associated with the student for the current academic year.
     */
    public function psychosocialCard(): HasOne
    {
        return $this
            ->hasOne(StudentPsychosocialCard::class)
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    // TODO: Add academic records relationship when the model and migration are implemented.
    // public function academicRecords(): HasMany
    // {
    //     return $this->hasMany(AcademicRecord::class);
    // }

    /**
     * Get all book distribution sessions that include this student across all academic years.
     */
    public function bookDistributions(): HasManyThrough
    {
        return $this->hasManyThrough(
            BookDistribution::class,
            BookDistributionItem::class,
            'student_id',
            'id',
            'id',
            'book_distribution_id',
        );
    }

    /**
     * Get the book distribution session associated with the student for the current academic year.
     */
    public function bookDistribution(): HasOneThrough
    {
        return $this->hasOneThrough(
            BookDistribution::class,
            BookDistributionItem::class,
            'student_id',
            'id',
            'id',
            'book_distribution_id',
        )->where('book_distribution_items.academic_year_id', '=', AcademicYear::currentId());
    }

    /**
     * Get all book distribution items associated with the student across all academic years.
     */
    public function bookDistributionItems(): HasMany
    {
        return $this->hasMany(BookDistributionItem::class);
    }

    /**
     * Get the book distribution item associated with the student for the current academic year.
     */
    public function bookDistributionItem(): HasOne
    {
        return $this
            ->hasOne(BookDistributionItem::class)
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return true;
    }

    public function hasEnrollment(): bool
    {
        if (array_key_exists('has_enrollment', $this->attributes)) {
            return (bool) $this->attributes['has_enrollment'];
        }

        if (array_key_exists('enrollment_exists', $this->attributes)) {
            return (bool) $this->attributes['enrollment_exists'];
        }

        if ($this->relationLoaded('enrollment')) {
            return ! is_null($this->getRelation('enrollment'));
        }

        return once(function (): bool {
            return $this->enrollment()->exists();
        });
    }

    public function doesntHaveEnrollment(): bool
    {
        return ! $this->hasEnrollment();
    }

    public function isAwaitingSchoolTransfer(): bool
    {
        $this->loadMissing(['transfer']);

        $transfer = $this->transfer;

        if (is_null($transfer)) {
            return false;
        }

        return ! is_null($transfer->left_academic_year_id)
            && ! is_null($transfer->from_school_id)
            && ! is_null($transfer->left_school_at)
            && is_null($transfer->joined_academic_year_id)
            && is_null($transfer->to_school_id)
            && is_null($transfer->joined_school_at);
    }

    /*
     * End: Custom Functions
     */
}
