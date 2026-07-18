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
 * @property int $education_monitor_id
 * @property int $school_id
 * @property int $grade_level_id
 * @property int $warehouse_id
 * @property Carbon $distributed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read EducationMonitor $educationMonitor
 * @property-read School $school
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
            'education_monitor_id' => 'integer',
            'school_id' => 'integer',
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
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('academic_year_id', '=', $academicYearId);
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
        $schoolId = auth('school')->user()?->organization_id;

        if (is_null($schoolId)) {
            return $query;
        }

        return $query->where('school_id', '=', $schoolId);
    }

    #[Scope]
    protected function forCurrentSchoolGradeLevel(Builder $query, int $gradeLevelId): Builder
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('academic_year_id', '=', $academicYearId);

        $schoolId = auth('school')->user()?->organization_id;

        if (! is_null($schoolId)) {
            $query->where('school_id', '=', $schoolId);
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

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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
