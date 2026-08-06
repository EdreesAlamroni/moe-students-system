<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $academic_year_id
 * @property int $school_period_id
 * @property int $grade_level_id
 * @property int $warehouse_id
 * @property Carbon $distributed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read SchoolPeriod $schoolPeriod
 * @property-read GradeLevel $gradeLevel
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, BookDistributionItem> $items
 * @property-read int|null $items_count
 */
#[Guarded(['id'])]
class BookDistribution extends Model
{
    /** @use HasFactory<\Database\Factories\BookDistributionFactory> */
    use HasFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'school_period_id' => 'integer',
            'grade_level_id' => 'integer',
            'warehouse_id' => 'integer',
            'distributed_at' => 'datetime',
        ];
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentAcademicYear(Builder $query): Builder
    {
        return $query->where('academic_year_id', '=', AcademicYear::currentConstraintId());
    }

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $warehouseId = auth('warehouse')->user()?->organization_id;

        if (is_null($warehouseId)) {
            return $query;
        }

        return $query->where('warehouse_id', '=', $warehouseId);
    }

    #[Scope]
    protected function forCurrentSchool(Builder $query): Builder
    {
        $id = auth('school')->user()?->organization_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('school_period_id', '=', $id);
    }

    #[Scope]
    protected function forCurrentSchoolGradeLevel(Builder $query, int $gradeLevelId): Builder
    {
        $query->where('academic_year_id', '=', AcademicYear::currentConstraintId());

        $id = auth('school')->user()?->organization_id;

        if (! is_null($id)) {
            $query->where('school_period_id', '=', $id);
        }

        return $query->where('grade_level_id', '=', $gradeLevelId);
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

    public function schoolPeriod(): BelongsTo
    {
        return $this->belongsTo(SchoolPeriod::class)->withTrashed();
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookDistributionItem::class);
    }

    /*
     * End: Relations
     */
}
