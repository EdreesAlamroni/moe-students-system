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
 * @property int $id
 * @property string $uuid
 * @property int|null $left_academic_year_id
 * @property int|null $joined_academic_year_id
 * @property int $student_id
 * @property int|null $from_school_id
 * @property int|null $to_school_id
 * @property Carbon|null $left_school_at
 * @property Carbon|null $joined_school_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read AcademicYear|null $leftAcademicYear
 * @property-read AcademicYear|null $joinedAcademicYear
 * @property-read Student $student
 * @property-read School|null $fromSchool
 * @property-read School|null $toSchool
 */
#[Guarded(['id'])]
class StudentTransfer extends Model
{
    /** @use HasFactory<\Database\Factories\StudentTransferFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'left_academic_year_id' => 'integer',
            'joined_academic_year_id' => 'integer',
            'student_id' => 'integer',
            'from_school_id' => 'integer',
            'to_school_id' => 'integer',
            'left_school_at' => 'datetime',
            'joined_school_at' => 'datetime',
        ];
    }

    /*
     * Start: Relationships
     */

    public function leftAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'left_academic_year_id');
    }

    public function joinedAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'joined_academic_year_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'from_school_id');
    }

    public function toSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'to_school_id');
    }

    /*
     * End: Relationships
     */
}
