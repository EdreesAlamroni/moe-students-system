<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $grade_level_id
 * @property int $school_id
 * @property int $academic_year_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table('grade_level_school')]
#[Guarded(['id'])]
class GradeLevelSchool extends Pivot
{
    public $incrementing = true;

    protected $with = ['academicYear'];

    protected function casts(): array
    {
        return [
            'grade_level_id' => 'integer',
            'school_id' => 'integer',
            'academic_year_id' => 'integer',
        ];
    }

    /*
     * Start: Relations
     */

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /*
     * End: Relations
     */
}
