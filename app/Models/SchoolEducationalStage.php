<?php

namespace App\Models;

use App\Enums\SchoolEducationalStageEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property int $school_id
 * @property SchoolEducationalStageEnum $stage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Guarded(['id'])]
class SchoolEducationalStage extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolEducationalStageFactory> */
    use HasFactory;

    protected function casts()
    {
        return [
            'academic_year_id' => 'integer',
            'school_id' => 'integer',
            'stage' => SchoolEducationalStageEnum::class,
        ];
    }

    /*
     * Start: Relationships
     */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /*
     * End: Relationships
     */
}
