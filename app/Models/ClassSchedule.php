<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $school_id
 * @property int $academic_year_id
 * @property int $classroom_id
 * @property int $class_period_id
 * @property int $subject_id
 * @property DayOfWeek $day_of_week
 * @property string $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read School $school
 * @property-read Classroom $classroom
 * @property-read ClassPeriod $classPeriod
 * @property-read Subject $subject
 */
#[Guarded(['id'])]
class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'school_id' => 'integer',
            'academic_year_id' => 'integer',
            'classroom_id' => 'integer',
            'class_period_id' => 'integer',
            'subject_id' => 'integer',
            'day_of_week' => DayOfWeek::class,
        ];
    }

    /*
     * Start: Accessors & Mutators
     */

    public function dayOfWeekLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->day_of_week->label();
        });
    }

    /*
     * End: Accessors & Mutators
     */

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

        return $query->where('school_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentSchoolAndAcademicYear(Builder $query): Builder
    {
        $id = auth('school')->user()->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->where('school_id', '=', $id);
    }

    #[Scope]
    protected function forClassroom(Builder $query, Classroom|int $classroom): Builder
    {
        $id = $classroom instanceof Classroom ? $classroom->id : $classroom;

        return $query->where('classroom_id', '=', $id);
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

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classPeriod(): BelongsTo
    {
        return $this->belongsTo(ClassPeriod::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return false;
    }

    /*
     * End: Custom Functions
     */
}
