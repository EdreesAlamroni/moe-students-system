<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read int|null $monitors_count
 * @property-read int|null $schools_count
 */
#[Guarded(['id'])]
class Warehouse extends Model
{
    /** @use HasFactory<\Database\Factories\WarehouseFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function ordered(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function users(): MorphMany
    {
        return $this->morphMany(User::class, 'model', 'model_type', 'model_id');
    }

    public function monitors(): HasMany
    {
        return $this->hasMany(EducationMonitor::class, 'warehouse_id');
    }

    public function schools(): HasManyThrough
    {
        return $this->hasManyThrough(
            School::class,
            EducationMonitor::class,
            'warehouse_id',
            'education_monitor_id',
            'id',
            'id',
        );
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return $this->monitors()->exists();
    }

    public static function list(?callable $callback = null, array $additionalColumns = ['id', 'name']): Collection
    {
        $columns = array_unique(
            array_merge(['id', 'name'], $additionalColumns)
        );

        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->ordered()
            ->pluck('name', 'id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    public function hasCoordinates(): bool
    {
        return filled($this->latitude) && filled($this->longitude);
    }

    public function syncEducationMonitors(array $monitorIds): void
    {
        $this->monitors()
            ->when(filled($monitorIds), function ($query) use ($monitorIds) {
                $query->whereNotIn('id', $monitorIds);
            })
            ->update(['warehouse_id' => null]);

        if ($monitorIds === []) {
            return;
        }

        EducationMonitor::query()
            ->whereIn('id', $monitorIds)
            ->update(['warehouse_id' => $this->id]);
    }

    /*
     * End: Custom Functions
     */
}
