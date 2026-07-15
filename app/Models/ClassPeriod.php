<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolAcademicPeriod;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $academic_year_id
 * @property SchoolAcademicPeriod $academic_period
 * @property string $name
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property int $order
 * @property bool $is_break
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property-read string $type
 * @property AcademicYear $academicYear
 * @property-read Collection<int, ClassSchedule> $schedules
 */
#[Guarded(['id'])]
class ClassPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\ClassPeriodFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'academic_period' => SchoolAcademicPeriod::class,
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'order' => 'integer',
            'is_break' => 'boolean',
        ];
    }

    /*
     * Start: Accessors & Mutators
     */

    public function type(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->is_break ? 'إستراحة' : 'حصة';
        });
    }

    /*
     * End: Accessors & Mutators
     */

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentAcademicYear(Builder $query): Builder
    {
        return $query->where('academic_year_id', '=', AcademicYear::currentId());
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN academic_period = 'morning' THEN 1 WHEN academic_period = 'evening' THEN 2 ELSE 3 END")
            ->orderBy('order', 'asc');
    }

    #[Scope]
    protected function forAcademicPeriod(Builder $query, SchoolAcademicPeriod $academicPeriod): Builder
    {
        return $query->where('academic_period', '=', $academicPeriod);
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

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return $this->schedules()->exists();
    }

    /**
     * Get the next available order value for a given academic period.
     */
    public static function getNextOrder(SchoolAcademicPeriod $academicPeriod): int
    {
        $maxOrder = self::query()
            ->forCurrentAcademicYear()
            ->forAcademicPeriod($academicPeriod)
            ->max('order');

        return ($maxOrder ?? 0) + 1;
    }

    /*
     * End: Custom Functions
     */
}
