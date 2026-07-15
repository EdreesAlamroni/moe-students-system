<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property-read string $uuid
 * @property-read int $academic_year_id
 * @property-read int|null $school_id
 * @property-read int $grade_level_id
 * @property-read int|null $classroom_id
 * @property-read int $student_id
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read AcademicYear $academicYear
 * @property-read School|null $school
 * @property-read GradeLevel $gradeLevel
 * @property-read Classroom|null $classroom
 * @property-read Student $student
 */
#[Guarded(['id'])]
class StudentEnrollment extends Model
{
    /** @use HasFactory<\Database\Factories\StudentEnrollmentFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'school_id' => 'integer',
            'grade_level_id' => 'integer',
            'classroom_id' => 'integer',
            'student_id' => 'integer',
        ];
    }

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

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /*
     * End: Relations
     */
}
