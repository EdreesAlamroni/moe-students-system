<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $book_distribution_id
 * @property int $student_id
 * @property int $academic_year_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read BookDistribution $bookDistribution
 * @property-read Student $student
 * @property-read AcademicYear $academicYear
 */
#[Guarded(['id'])]
class BookDistributionItem extends Model
{
    /** @use HasFactory<\Database\Factories\BookDistributionItemFactory> */
    use HasFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'book_distribution_id' => 'integer',
            'student_id' => 'integer',
            'academic_year_id' => 'integer',
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
            return $query;
        }

        return $query->where('academic_year_id', '=', $academicYearId);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function bookDistribution(): BelongsTo
    {
        return $this->belongsTo(BookDistribution::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /*
     * End: Relations
     */
}
