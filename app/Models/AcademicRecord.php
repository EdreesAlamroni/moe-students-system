<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\AcademicRecordRating;
use App\Enums\AcademicRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $student_id
 * @property int $grade_level_id
 * @property int $academic_year_id
 * @property AcademicRecordStatus $status
 * @property AcademicRecordRating|null $rating
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property AcademicYear $academicYear
 * @property GradeLevel $gradeLevel
 * @property Student $student
 */
#[Guarded(['id'])]
class AcademicRecord extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicRecordFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => AcademicRecordStatus::class,
            'rating' => AcademicRecordRating::class,
        ];
    }

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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /*
     * End: Relations
     */
}
