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
 * @property int $school_period_id
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

    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'school_period_id' => 'integer',
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

    public function schoolPeriod(): BelongsTo
    {
        return $this->belongsTo(SchoolPeriod::class);
    }

    /*
     * End: Relationships
     */
}
