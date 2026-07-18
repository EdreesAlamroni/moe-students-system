<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolEducationalStageEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property string $code
 * @property string $name
 * @property SchoolEducationalStageEnum $educational_stage
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read int|null $student_count
 */
#[Guarded(['id'])]
class GradeLevel extends Model
{
    /** @use HasFactory<\Database\Factories\GradeLevelFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'educational_stage' => SchoolEducationalStageEnum::class,
            'order' => 'integer',
        ];
    }

    /*
     * Start: Accessors & Mutators
     */

    /**
     * Returns the current academic year name for this grade level within the current school context.
     *
     * This accessor behaves as follows:
     * 1. If the `schools` relation is **eager-loaded** with an academic_year join
     *    (i.e., selecting `academic_years.name AS academic_year_name`),
     *    it returns the `academic_year_name` from the first loaded record.
     * 2. Otherwise, if the model already has an `academic_year_name` attribute
     *    (e.g. selected via a custom query), it returns that.
     * 3. If neither condition is met, it gracefully returns `null`.
     *
     * Use this accessor in your controllers or transformers as:
     * ```php
     *  $gradeLevel->currentAcademicYearName;
     * ```
     */
    public function currentAcademicYearName(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->relationLoaded('schools')) {
                // If the relation is loaded, return the academic_year_name from the first related school
                return $this->schools->value('academic_year_name');
            }

            if ($this->hasAttribute('academic_year_name')) {
                // If the attribute is already set on the model, return it
                return $this->getAttribute('academic_year_name');
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
    protected function forCurrentSchoolAndAcademicYear(Builder $query): Builder
    {
        $id = auth('school')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->join('grade_level_school', function (JoinClause $join) use ($id) {
            $join->on('grade_levels.id', '=', 'grade_level_school.grade_level_id')
                ->where('grade_level_school.academic_year_id', '=', AcademicYear::currentId())
                ->where('grade_level_school.school_id', '=', $id);
        });
    }

    #[Scope]
    protected function forCurrentEducationMonitor(Builder $query): Builder
    {
        $id = auth('education_monitor')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->whereExists(function ($subquery) use ($id) {
            $subquery->selectRaw('1')
                ->from('grade_level_school')
                ->join('schools', 'schools.id', '=', 'grade_level_school.school_id')
                ->whereColumn('grade_level_school.grade_level_id', 'grade_levels.id')
                ->where('grade_level_school.academic_year_id', '=', AcademicYear::currentId())
                ->where('schools.education_monitor_id', '=', $id);
        });
    }

    #[Scope]
    protected function forCurrentEducationServicesOffice(Builder $query): Builder
    {
        $id = auth('education_services_office')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->whereExists(function ($subquery) use ($id) {
            $subquery->selectRaw('1')
                ->from('grade_level_school')
                ->join('schools', 'schools.id', '=', 'grade_level_school.school_id')
                ->whereColumn('grade_level_school.grade_level_id', 'grade_levels.id')
                ->where('grade_level_school.academic_year_id', '=', AcademicYear::currentId())
                ->where('schools.education_services_office_id', '=', $id);
        });
    }

    #[Scope]
    protected function ordered(Builder $query, string $direction = 'asc'): Builder
    {
        $stages = SchoolEducationalStageEnum::orderedValues();

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
            return $query
                ->orderBy('educational_stage')
                ->orderBy('order', $direction);
        }

        return $query
            ->orderByRaw('FIELD(educational_stage, ?, ?, ?)', $stages)
            ->orderBy('order', $direction);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    /**
     * Get all schools associated with the grade level across all academic years.
     */
    public function allSchools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'grade_level_school')
            ->using(GradeLevelSchool::class)
            ->withPivot(['academic_year_id'])
            ->withTimestamps();
    }

    /**
     * Get the schools associated with the grade level for the current academic year.
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'grade_level_school')
            ->using(GradeLevelSchool::class)
            ->withPivot(['academic_year_id'])
            ->wherePivot('academic_year_id', '=', AcademicYear::currentId())
            ->withTimestamps();
    }

    /**
     * Get the current school of the authenticated user associated with their grade level for the current academic year.
     */
    public function currentSchool(): HasOneThrough
    {
        $schoolId = auth('school')->user()->organization_id;

        return $this
            ->hasOneThrough(
                School::class,
                GradeLevelSchool::class,
                'grade_level_id',
                'id',
                'id',
                'school_id'
            )
            ->where('grade_level_school.academic_year_id', AcademicYear::currentId())
            ->where('grade_level_school.school_id', $schoolId);
    }

    /**
     * Get all classrooms associated with this grade level across all academic years.
     */
    public function allClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'grade_level_id');
    }

    /**
     * Get the classrooms associated with this grade level for the current academic year.
     */
    public function classrooms(): HasMany
    {
        return $this
            ->hasMany(Classroom::class, 'grade_level_id')
            ->where('academic_year_id', '=', AcademicYear::currentId());
    }

    /**
     * Get all students associated with the grade level across all academic years.
     */
    public function allStudents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'grade_level_id',
            'id',
            'id',
            'student_id',
        );
    }

    /**
     * Get the students associated with the grade level for the current academic year.
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'grade_level_id',
            'id',
            'id',
            'student_id',
        )->where('academic_year_id', '=', AcademicYear::currentId());
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

    public static function list(?callable $callback = null, array $additionalColumns = []): Collection
    {
        $columns = array_unique(
            array_merge(['grade_levels.id', 'grade_levels.name', 'grade_levels.order'], $additionalColumns)
        );
        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->orderBy('grade_levels.order')
            ->pluck('grade_levels.name', 'grade_levels.id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    /*
     * End: Custom Functions
     */
}
