<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string $uuid
 * @property int $grade_level_id
 * @property string $name
 * @property string $code
 * @property bool $included_in_total_score
 * @property bool $needs_lab
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $included_in_total_score_label
 * @property-read string $needs_lab_label
 */
#[Guarded(['id'])]
class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    #[Override]
    protected function casts()
    {
        return [
            'grade_level_id' => 'integer',
            'included_in_total_score' => 'boolean',
            'needs_lab' => 'boolean',
        ];
    }

    /*
     * Start: Accessors & Mutators
     */

    public function includedInTotalScoreLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->included_in_total_score ? __('نعم') : __('لا');
        });
    }

    public function needsLabLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->needs_lab ? __('نعم') : __('لا');
        });
    }

    /*
     * End: Accessors & Mutators
     */

    /*
     * Start: Relations
     */

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return false;
    }

    /*
     * End: Custom Functions
     */

}
