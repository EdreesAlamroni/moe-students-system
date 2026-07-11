<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read int $schools_count
 */
#[Guarded(['id'])]
class Municipal extends Model
{
    /** @use HasFactory<\Database\Factories\MunicipalFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    /*
     * Start: Relations
     */

    public function monitor(): HasOne
    {
        return $this->hasOne(EducationMonitor::class, 'municipal_id');
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public static function list(?callable $callback = null, array $additionalColumns = []): Collection
    {
        $columns = array_unique(
            array_merge(['id', 'name'], $additionalColumns)
        );

        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->orderBy('name')
            ->pluck('name', 'id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    public static function listUnassigned(array|int|null $except = null): Collection
    {
        $exceptIds = Arr::wrap($except);

        return self::list(function (Builder $query) use ($exceptIds) {
            $query->whereDoesntHave('monitor', function (Builder $query) use ($exceptIds): void {
                $query->when(filled($exceptIds), function (Builder $query) use ($exceptIds): void {
                    $query->whereNotIn('education_monitors.id', $exceptIds);
                })->withoutGlobalScope(SoftDeletingScope::class);
            });
        });
    }

    /*
     * End: Custom Functions
     */
}
