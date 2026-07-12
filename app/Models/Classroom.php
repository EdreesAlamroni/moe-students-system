<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
        $id = auth('school')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('school_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentSchoolAndAcademicYear(Builder $query): Builder
    {
        $id = auth('school')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->where('school_id', '=', $id);
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

    // TODO: Add schedules relationship when the model and migration are implemented.
    // public function schedules(): HasMany
    // {
    //     return $this->hasMany(ClassSchedule::class);
    // }

    // TODO: Add students relationship when the model and migration are implemented.
    /**
     * Get all students associated with the classroom across all academic years.
     */
    // public function allStudents(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         Student::class,
    //         StudentEnrollment::class,
    //         'classroom_id',
    //         'id',
    //         'id',
    //         'student_id',
    //     );
    // }

    // TODO: Add students relationship when the model and migration are implemented.
    /**
     * Get the students associated with the classroom for the current academic year.
     */
    // public function students(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         Student::class,
    //         StudentEnrollment::class,
    //         'classroom_id',
    //         'id',
    //         'id',
    //         'student_id',
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
    // public static function listForCurrentSchool(?int $gradeLevelId = null): Collection
    // {
    //     return self::query()
    //         ->select([
    //             'classrooms.id',
    //             'classrooms.name',
    //             'classrooms.grade_level_id',
    //         ])
    //         ->forCurrentSchoolAndAcademicYear()
    //         ->when(filled($gradeLevelId), function (Builder $query) use ($gradeLevelId) {
    //             $query->where('grade_level_id', '=', $gradeLevelId);
    //         })
    //         ->pluck('classrooms.name', 'classrooms.id')
    //         ->map(function (string $name, int $id): array {
    //             return [
    //                 'id' => $id,
    //                 'name' => sprintf('الفصل الدراسي: %s', $name),
    //             ];
    //         })->values();
    // }

    /*
     * End: Custom Functions
     */
}
