<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $school_id
 * @property int $academic_year_id
 * @property Carbon $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read AcademicYear $academicYear
 */
#[Guarded(['id'])]
class ClassroomDistributionCompletion extends Model
{
    protected function casts(): array
    {
        return [
            'school_id' => 'integer',
            'academic_year_id' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /*
     * Start: Relations
     */

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public static function isCompleteForSchoolAndYear(int $schoolId, int $academicYearId): bool
    {
        return self::query()
            ->where('school_id', '=', $schoolId)
            ->where('academic_year_id', '=', $academicYearId)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public static function isCompleteForCurrentSchoolAndYear(): bool
    {
        $schoolId = auth('school')->user()->organization_id;

        if (is_null($schoolId)) {
            return false;
        }

        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return false;
        }

        return self::isCompleteForSchoolAndYear($schoolId, $academicYearId);
    }

    /*
     * End: Custom Functions
     */
}
