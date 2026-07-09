<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Enums\SchoolEducationalStageEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property string $code
 * @property string $name
 * @property SchoolEducationalStageEnum $educational_stage
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Guarded(['id'])]
class GradeLevel extends Model
{
    /** @use HasFactory<\Database\Factories\GradeLevelFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'educational_stage' => SchoolEducationalStageEnum::class,
            'order' => 'integer',
        ];
    }

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return true;
    }

    public static function list(?callable $callback = null, array $additionalColumns = []): Collection
    {
        $columns = array_unique(
            array_merge(['grade_levels.id', 'grade_levels.name', 'grade_levels.order'], $additionalColumns)
        );
        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->orderBy('grade_levels.order')
            ->pluck('grade_levels.name', 'grade_levels.id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    /*
     * End: Custom Functions
     */
}
