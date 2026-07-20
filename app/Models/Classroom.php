<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolEducationalStageEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property int $academic_year_id
 * @property int $school_id
 * @property int $grade_level_id
 * @property string $name
 * @property int $capacity
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property-read AcademicYear $academicYear
 * @property-read GradeLevel $gradeLevel
 * @property-read School $school
 * @property-read EloquentCollection<ClassSchedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read EloquentCollection<Student> $allStudents
 * @property-read EloquentCollection<Student> $students
 * @property-read int $students_count
 */
#[Guarded(['id'])]
class Classroom extends Model
{
    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'school_id' => 'integer',
            'grade_level_id' => 'integer',
        ];
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentSchool(Builder $query): Builder
    {
        $id = auth('school')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        $table = $query->getModel()->getTable();

        return $query->where("{$table}.school_id", '=', $id);
    }

    #[Scope]
    protected function forCurrentSchoolAndAcademicYear(Builder $query): Builder
    {
        $id = auth('school')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        $table = $query->getModel()->getTable();

        return $query
            ->where("{$table}.academic_year_id", '=', AcademicYear::currentId())
            ->where("{$table}.school_id", '=', $id);
    }

    #[Scope]
    protected function ordered(Builder $query, string $direction = 'asc'): Builder
    {
        $table = $query->getModel()->getTable();

        $query->join('grade_levels', function (JoinClause $join) use ($table): void {
            $join
                ->on('grade_levels.id', '=', "{$table}.grade_level_id")
                ->whereNull('grade_levels.deleted_at');
        });

        $columns = $query->getQuery()->columns;

        if ($columns === null) {
            $query->select("{$table}.*");
        } else {
            $qualifiedColumns = [];

            foreach ($columns as $column) {
                if (is_string($column) && ! str_contains($column, '.')) {
                    $qualifiedColumns[] = "{$table}.{$column}";
                } else {
                    $qualifiedColumns[] = $column;
                }
            }

            $query->select($qualifiedColumns);
        }

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();
        $stages = SchoolEducationalStageEnum::orderedValues();

        if (in_array($connection->getDriverName(), ['sqlite', 'pgsql'], true)) {
            return $query
                ->orderBy('grade_levels.educational_stage')
                ->orderBy('grade_levels.order', $direction)
                ->orderBy("{$table}.name", $direction);
        }

        return $query
            ->orderByRaw('FIELD(grade_levels.educational_stage, ?, ?, ?)', $stages)
            ->orderBy('grade_levels.order', $direction)
            ->orderByRaw("{$table}.name COLLATE utf8mb4_unicode_ci {$direction}");
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    /**
     * Get all students associated with the classroom across all academic years.
     */
    public function allStudents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'classroom_id',
            'id',
            'id',
            'student_id',
        );
    }

    /**
     * Get the students associated with the classroom for the current academic year.
     */
    public function students(): HasManyThrough
    {
        $table = $this->getTable();

        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'classroom_id',
            'id',
            'id',
            'student_id',
        )->whereColumn('student_enrollments.academic_year_id', "{$table}.academic_year_id");
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
            array_merge(['classrooms.id', 'classrooms.name'], $additionalColumns)
        );
        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->pluck('classrooms.name', 'classrooms.id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    // TODO: Remove this function if not needed
    public static function listForCurrentSchool(?int $gradeLevelId = null): Collection
    {
        return self::query()
            ->select([
                'classrooms.id',
                'classrooms.name',
                'classrooms.grade_level_id',
            ])
            ->forCurrentSchoolAndAcademicYear()
            ->when(filled($gradeLevelId), function (Builder $query) use ($gradeLevelId) {
                $query->where('grade_level_id', '=', $gradeLevelId);
            })
            ->pluck('classrooms.name', 'classrooms.id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => sprintf('الفصل الدراسي: %s', $name),
                ];
            })->values();
    }

    /*
     * End: Custom Functions
     */
}
