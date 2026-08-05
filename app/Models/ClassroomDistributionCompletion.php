<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property int $school_period_id
 * @property Carbon $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SchoolPeriod $schoolPeriod
 * @property-read AcademicYear $academicYear
 */
#[Guarded(['id'])]
class ClassroomDistributionCompletion extends Model
{
    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'school_period_id' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /*
     * Start: Relations
     */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolPeriod(): BelongsTo
    {
        return $this->belongsTo(SchoolPeriod::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public static function isCompleteForSchoolPeriodAndYear(int $academicYearId, int $schoolPeriodId): bool
    {
        return self::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('school_period_id', '=', $schoolPeriodId)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public static function isCompleteForCurrentSchoolAndYear(): bool
    {
        $schoolPeriodId = auth('school')->user()->organization_id;

        if (is_null($schoolPeriodId)) {
            return false;
        }

        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return false;
        }

        return self::isCompleteForSchoolPeriodAndYear($currentAcademicYearId, $schoolPeriodId);
    }

    /*
     * End: Custom Functions
     */
}
