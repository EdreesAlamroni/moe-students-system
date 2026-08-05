<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property string|null $educational_company_name
 * @property SchoolBranchType|null $branch_type
 * @property SchoolBuildingType|null $building_type
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string|null $academic_period_label
 * @property-read EducationMonitor $monitor
 * @property-read EducationServicesOffice|null $office
 * @property-read EloquentCollection<int, SchoolPeriod> $periods
 * @property-read EloquentCollection<int, SchoolEducationalStage> $allEducationalStages
 * @property-read EloquentCollection<int, SchoolEducationalStage> $educationalStages
 */
#[Guarded(['id'])]
class School extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    private const PERIOD_DENORMALIZED_COLUMNS = [
        'education_monitor_id',
        'education_services_office_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'education_monitor_id' => 'integer',
            'education_services_office_id' => 'integer',
            'type' => SchoolType::class,
            'branch_type' => SchoolBranchType::class,
            'building_type' => SchoolBuildingType::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $school): void {
            $count = (string) (self::query()->withTrashed()->count() + 1);
            $serialNumber = Str::padLeft($count, 6, '0');
            $school->serial_number = $serialNumber;
        });

        static::saved(function (self $school): void {
            if (! $school->wasChanged(self::PERIOD_DENORMALIZED_COLUMNS)) {
                return;
            }

            SchoolPeriod::query()
                ->withTrashed()
                ->where('school_id', '=', $school->id)
                ->update($school->periodDenormalizedAttributes());
        });
    }

    /*
     * Start: Accessors & Mutators
     */

    /**
     * Human-readable academic period label for list and report views.
     *
     * Resolves from the eager-loaded `periods` relation when present.
     */
    public function academicPeriodLabel(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->relationLoaded('periods')) {
                return match ($this->periods->count()) {
                    0 => null,
                    1 => $this->periods->first()->academic_period->name(),
                    default => SchoolAcademicPeriod::DUAL_PERIOD->name(),
                };
            }

            if (array_key_exists('periods_count', $this->attributes)) {
                return match ($this->periods_count) {
                    0 => null,
                    1 => 'فترة واحدة', // Can't know which period without loading it.
                    default => SchoolAcademicPeriod::DUAL_PERIOD->name(),
                };
            }

            return null;
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

        return $query->where('education_services_office_id', '=', $id);
    }

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
    protected function ordered(Builder $query): Builder
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
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

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(EducationServicesOffice::class, 'education_services_office_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(SchoolPeriod::class);
    }

    public function morningPeriod(): HasOne
    {
        return $this
            ->hasOne(SchoolPeriod::class)
            ->where('academic_period', '=', SchoolAcademicPeriod::MORNING);
    }

    public function eveningPeriod(): HasOne
    {
        return $this
            ->hasOne(SchoolPeriod::class)
            ->where('academic_period', '=', SchoolAcademicPeriod::EVENING);
    }

    public function allEducationalStages(): HasManyThrough
    {
        return $this->hasManyThrough(
            SchoolEducationalStage::class,
            SchoolPeriod::class,
            'school_id',
            'school_period_id',
        );
    }

    public function educationalStages(): HasManyThrough
    {
        return $this
            ->allEducationalStages()
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

    /**
     * Grade levels the school can still add for the current academic year, limited to its
     * educational stages and excluding those assigned to any of its periods.
     *
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
            ->whereIn('school_period_id', $this->periods()->select('id'));

        return GradeLevel::list(function (Builder $query) use ($stages, $assignedGradeLevelIds): void {
            $query
                ->whereIn('grade_levels.educational_stage', $stages)
                ->whereNotIn('grade_levels.id', $assignedGradeLevelIds);
        });
    }

    public function periodDenormalizedAttributes(): array
    {
        return [
            'education_monitor_id' => $this->education_monitor_id,
            'education_services_office_id' => $this->education_services_office_id,
            'name' => $this->name,
        ];
    }

    public function currentGradeLevelIds(): array
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return [];
        }

        $periodIds = $this->periods()->pluck('school_periods.id')->all();

        return GradeLevelSchoolPeriod::query()
            ->select(['academic_year_id', 'school_period_id', 'grade_level_id'])
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('school_period_id', $periodIds)
            ->distinct()
            ->pluck('grade_level_id')
            ->all();
    }

    /*
     * End: Custom Functions
     */
}
