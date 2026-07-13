<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $education_monitor_id
 * @property int|null $education_services_office_id
 * @property string $serial_number
 * @property SchoolType $type
 * @property SchoolBranchType|null $branch_type
 * @property string|null $educational_company_name
 * @property SchoolBuildingType|null $building_type
 * @property string $name
 * @property SchoolAcademicPeriod|null $academic_period
 * @property SchoolStudentsGender|null $students_gender
 * @property string|null $phone_number
 * @property string|null $whatsapp_phone_number
 * @property string|null $address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read EducationMonitor $monitor
 * @property-read EducationServicesOffice|null $office
 */
#[Guarded(['id'])]
class School extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'education_monitor_id' => 'integer',
            'education_services_office_id' => 'integer',
            'type' => SchoolType::class,
            'branch_type' => SchoolBranchType::class,
            'building_type' => SchoolBuildingType::class,
            'academic_period' => SchoolAcademicPeriod::class,
            'students_gender' => SchoolStudentsGender::class,
        ];
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function (self $school) {
            $count = strval(self::query()->withTrashed()->count() + 1);
            $serialNumber = Str::padLeft($count, 6, '0');
            $school->serial_number = $serialNumber;
        });
    }

    /*
     * Start: Accessors & Mutators
     */

    public function formattedWhatsappPhoneNumber(): Attribute
    {
        return Attribute::get(function (): ?string {
            $phoneNumber = $this->whatsapp_phone_number;

            if (blank($phoneNumber)) {
                return null;
            }

            return Str::of($phoneNumber)->ltrim('0')->prepend('+218')->toString();
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
        $id = auth('education_monitor')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('education_monitor_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentEducationServicesOffice(Builder $query): Builder
    {
        $id = auth('education_services_office')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('education_services_office_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $id = auth('warehouse')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->whereHas('monitor', function (Builder $query) use ($id): void {
            $query->where('warehouse_id', '=', $id);
        });
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'sqlite') {
            return $query->orderBy('name');
        }

        return $query->orderByRaw('name COLLATE utf8mb4_unicode_ci ASC');
    }

    #[Scope]
    protected function orderedByMonitor(Builder $query, string $direction = 'asc'): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->join('education_monitors', 'education_monitors.id', '=', "{$table}.education_monitor_id")
            ->orderBy('education_monitors.name', $direction)
            ->orderBy("{$table}.name", $direction);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function users(): MorphMany
    {
        return $this->morphMany(User::class, 'model', 'model_type', 'model_id');
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
        return $this->hasMany(SchoolEducationalStage::class, 'school_id');
    }

    /**
     * Get the educational stages associated with the school for the current academic year.
     */
    public function educationalStages(): HasMany
    {
        return $this
            ->hasMany(SchoolEducationalStage::class, 'school_id')
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    /**
     * Get all grade levels associated with the school across all academic years.
     */
    public function allGradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_school')
            ->using(GradeLevelSchool::class)
            ->withPivot(['academic_year_id'])
            ->withTimestamps();
    }

    /**
     * Get the grade levels associated with the school for the current academic year.
     */
    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_school')
            ->using(GradeLevelSchool::class)
            ->withPivot(['academic_year_id'])
            ->wherePivot('academic_year_id', '=', AcademicYear::currentId())
            ->withTimestamps();
    }

    /**
     * Get all classrooms associated with this school across all academic years.
     */
    public function allClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'school_id');
    }

    /**
     * Get the classrooms associated with this school for the current academic year.
     */
    public function classrooms(): HasMany
    {
        return $this
            ->hasMany(Classroom::class, 'school_id')
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // TODO: Add enrollments relationship when the model and migration are implemented.
    /**
     * Get all enrollments associated with the school across all academic years.
     */
    // public function allEnrollments(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         StudentEnrollment::class,
    //         Student::class,
    //         'school_id',
    //         'student_id',
    //         'id',
    //         'id',
    //     );
    // }

    // TODO: Add enrollments relationship when the model and migration are implemented.
    /**
     * Get the enrollments associated with the school for the current academic year.
     */
    // public function enrollments(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         StudentEnrollment::class,
    //         Student::class,
    //         'school_id',
    //         'student_id',
    //         'id',
    //         'id',
    //     )->where('academic_year_id', '=', AcademicYear::currentId());
    // }

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

    public static function list(?callable $callback = null, array $additionalColumns = ['id', 'name']): Collection
    {
        $columns = array_unique(
            array_merge(['id', 'name'], $additionalColumns)
        );

        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->ordered()
            ->pluck('name', 'id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    public function nameWithMonitor(): string
    {
        $this->loadMissing(['monitor:id,name']);

        return "{$this->monitor->name} - {$this->name}";
    }

    public function printOrganizationLines(): array
    {
        $this->loadMissing(['monitor:id,name']);

        return [$this->monitor->name, $this->name];
    }

    public function isPublic(): bool
    {
        return $this->type->isPublic();
    }

    public function isPrivate(): bool
    {
        return $this->type->isPrivate();
    }

    public function isMorningPeriod(): bool
    {
        return $this->academic_period->isMorning();
    }

    public function isEveningPeriod(): bool
    {
        return $this->academic_period->isEvening();
    }

    public function isDualPeriod(): bool
    {
        return $this->academic_period->isDualPeriod();
    }

    public function isSinglePeriod(): bool
    {
        return $this->academic_period->isSinglePeriod();
    }

    /*
     * End: Custom Functions
     */
}
