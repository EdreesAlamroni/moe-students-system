<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolStudentsGender;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property int $education_monitor_id
 * @property int|null $education_services_office_id
 * @property int $school_id
 * @property string $name
 * @property SchoolAcademicPeriod $academic_period
 * @property SchoolStudentsGender|null $students_gender
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read EloquentCollection<int, User> $users
 * @property-read EducationMonitor $monitor
 * @property-read EducationServicesOffice|null $office
 * @property-read School $school
 * @property-read EloquentCollection<int, SchoolEducationalStage> $educationalStages
 * @property-read EloquentCollection<int, GradeLevel> $allGradeLevels
 * @property-read EloquentCollection<int, GradeLevel> $gradeLevels
 * @property-read EloquentCollection<int, Classroom> $allClassrooms
 * @property-read EloquentCollection<int, Classroom> $classrooms
 * @property-read EloquentCollection<int, Student> $students
 * @property-read EloquentCollection<int, StudentEnrollment> $allEnrollments
 * @property-read EloquentCollection<int, StudentEnrollment> $enrollments
 * @property-read EloquentCollection<int, ClassSchedule> $classSchedules
 * @property-read EloquentCollection<int, ClassroomDistributionCompletion> $classroomDistributionCompletions
 * @property-read EloquentCollection<int, StudentTransfer> $outgoingTransfers
 * @property-read EloquentCollection<int, StudentTransfer> $incomingTransfers
 * @property-read EloquentCollection<int, BookDistribution> $bookDistributions
 * @property-read EloquentCollection<int, BookDistributionItem> $bookDistributionItems
 * @property-read int|null $educational_stages_count
 * @property-read int|null $all_grade_levels_count
 * @property-read int|null $grade_levels_count
 * @property-read int|null $all_classrooms_count
 * @property-read int|null $classrooms_count
 * @property-read int|null $all_students_count
 * @property-read int|null $students_count
 * @property-read int|null $classrooms_count
 * @property-read int|null $all_enrollments_count
 * @property-read int|null $enrollments_count
 */
#[Guarded(['id'])]
class SchoolPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolPeriodFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'education_monitor_id' => 'integer',
            'education_services_office_id' => 'integer',
            'school_id' => 'integer',
            'academic_period' => SchoolAcademicPeriod::class,
            'students_gender' => SchoolStudentsGender::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $schoolPeriod): void {
            $school = School::query()
                ->select([
                    'id',
                    'education_monitor_id',
                    'education_services_office_id',
                    'name',
                ])
                ->findOrFail($schoolPeriod->school_id);

            $schoolPeriod->forceFill($school->periodDenormalizedAttributes());
        });
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $id = auth('warehouse')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->whereHas('monitor', function (Builder $query) use ($id): void {
            $query->where('warehouse_id', '=', $id);
        });
    }

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

        return $query->where('education_services_office_id', '=', $id);
    }

    #[Scope]
    protected function orderedByAcademicPeriod(Builder $query): Builder
    {
        return $query->orderByRaw(
            'CASE academic_period WHEN ? THEN 0 WHEN ? THEN 1 ELSE 2 END',
            [SchoolAcademicPeriod::MORNING->value, SchoolAcademicPeriod::EVENING->value],
        );
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
            return $query->orderBy('name');
        }

        return $query->orderByRaw('name COLLATE utf8mb4_unicode_ci ASC');
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function users(): MorphMany
    {
        return $this->morphMany(User::class, 'organization');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(EducationServicesOffice::class, 'education_services_office_id');
    }

    /**
     * Get all educational stages associated with the school across all academic years.
     */
    public function allEducationalStages(): HasMany
    {
        return $this->hasMany(SchoolEducationalStage::class, 'school_period_id');
    }

    /**
     * Get the educational stages associated with the school for the current academic year.
     */
    public function educationalStages(): HasMany
    {
        return $this
            ->hasMany(SchoolEducationalStage::class, 'school_period_id')
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    /**
     * Get all grade levels associated with the school across all academic years.
     *
     * @return BelongsToMany<GradeLevel, $this, GradeLevelSchoolPeriod>
     */
    public function allGradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_school_period')
            ->using(GradeLevelSchoolPeriod::class)
            ->withPivot(['academic_year_id'])
            ->withTimestamps();
    }

    /**
     * Get the grade levels associated with the school for the current academic year.
     *
     * @return BelongsToMany<GradeLevel, $this, GradeLevelSchoolPeriod>
     */
    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_school_period')
            ->using(GradeLevelSchoolPeriod::class)
            ->withPivot(['academic_year_id'])
            ->wherePivot('academic_year_id', '=', AcademicYear::currentId())
            ->withTimestamps();
    }

    /**
     * Get all classrooms associated with this school across all academic years.
     */
    public function allClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'school_period_id');
    }

    /**
     * Get the classrooms associated with this school for the current academic year.
     */
    public function classrooms(): HasMany
    {
        return $this
            ->hasMany(Classroom::class, 'school_period_id')
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all enrollments associated with the school across all academic years.
     */
    public function allEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get the enrollments associated with the school for the current academic year.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class)
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function classroomDistributionCompletions(): HasMany
    {
        return $this->hasMany(ClassroomDistributionCompletion::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class, 'from_school_period_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class, 'to_school_period_id');
    }

    public function bookDistributions(): HasMany
    {
        return $this->hasMany(BookDistribution::class);
    }

    public function bookDistributionItems(): HasMany
    {
        return $this->hasMany(BookDistributionItem::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public static function list(?callable $callback = null, array $additionalColumns = ['id', 'name']): Collection
    {
        $columns = array_unique(
            array_merge(['id', 'name', 'academic_period'], $additionalColumns)
        );

        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->ordered()
            ->get()
            ->map(function (self $schoolPeriod): array {
                return [
                    'id' => $schoolPeriod->id,
                    'name' => sprintf('%s (%s)', $schoolPeriod->name, $schoolPeriod->academic_period->displayName()),
                ];
            })->values();
    }

    public function isMorningPeriod(): bool
    {
        return $this->academic_period->isMorning();
    }

    public function isEveningPeriod(): bool
    {
        return $this->academic_period->isEvening();
    }

    public function isPublic(): bool
    {
        return $this->school->isPublic();
    }

    public function isPrivate(): bool
    {
        return $this->school->isPrivate();
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public function availableGradeLevels(): Collection
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return collect([]);
        }

        $stages = $this->educationalStages()->pluck('stage');

        if ($stages->isEmpty()) {
            return collect([]);
        }

        $assignedGradeLevelIds = GradeLevelSchoolPeriod::query()
            ->select(['grade_level_id'])
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn(
                'school_period_id',
                self::query()
                    ->select(['id'])
                    ->where('school_id', '=', $this->school_id)
            );

        return GradeLevel::list(function (Builder $query) use ($stages, $assignedGradeLevelIds): void {
            $query
                ->whereIn('grade_levels.educational_stage', $stages)
                ->whereNotIn('grade_levels.id', $assignedGradeLevelIds);
        });
    }

    public function printOrganizationLines(): array
    {
        $this->loadMissing(['monitor:id,name']);

        return [$this->monitor->name, $this->name];
    }

    /*
     * End: Custom Functions
     */
}
