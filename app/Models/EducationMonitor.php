<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $municipal_id
 * @property int|null $warehouse_id
 * @property string $name
 * @property string|null $phone_number
 * @property string|null $whatsapp_phone_number
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string|null $formatted_whatsapp_phone_number
 * @property-read int|null $offices_count
 * @property-read int|null $schools_count
 * @property-read int|null $students_count
 */
#[Guarded(['id'])]
class EducationMonitor extends Model
{
    /** @use HasFactory<\Database\Factories\EducationMonitorFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    private const GENERATED_NAME_PREFIX = 'مُراقبة التّربية والتّعليم';

    protected function casts(): array
    {
        return [
            'municipal_id' => 'integer',
            'warehouse_id' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public static function booted(): void
    {
        static::saving(function (self $monitor): void {
            $monitor->syncGeneratedName();
        });
    }

    /*
     * Start: Accessors & Mutators
     */

    public function formattedWhatsappPhoneNumber(): Attribute
    {
        return Attribute::get(function (): ?string {
            $phoneNumber = $this->whatsapp_phone_number;

            if (blank($phoneNumber)) {
                return null;
            }

            return Str::of($phoneNumber)->ltrim('0')->prepend('+218')->toString();
        });
    }

    /*
     * End: Accessors & Mutators
     */

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $id = auth('warehouse')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('warehouse_id', '=', $id);
    }

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

    public function municipal(): BelongsTo
    {
        return $this->belongsTo(Municipal::class, 'municipal_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function offices(): HasMany
    {
        return $this->hasMany(EducationServicesOffice::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    // TODO: Add students relationship
    // public function students(): HasManyThrough
    // {
    //     return $this->hasManyThrough(Student::class, School::class);
    // }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function hasAnyRelations(): bool
    {
        return true;
    }

    public function hasCoordinates(): bool
    {
        return filled($this->latitude) && filled($this->longitude);
    }

    public function printOrganizationLines(): array
    {
        return [$this->name];
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

    public function syncGeneratedName(): void
    {
        if (blank($this->municipal_id)) {
            return;
        }

        $municipalName = Municipal::query()
            ->whereKey($this->municipal_id)
            ->value('name');

        if (blank($municipalName)) {
            return;
        }

        $this->name = self::generateName($municipalName);
    }

    public static function generateName(string $municipalName): string
    {
        return Str::of(self::GENERATED_NAME_PREFIX)
            ->append(' ')
            ->append($municipalName)
            ->toString();
    }

    public static function listWithOffices(): Collection
    {
        return self::query()
            ->select(['id', 'name'])
            ->ordered()
            ->with(['offices:id,name,education_monitor_id'])
            ->get()
            ->map(function (EducationMonitor $monitor): array {
                return [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'offices' => $monitor->offices->only(['id', 'name'])->all(),
                ];
            })->values();
    }

    public static function listWithSchools(): Collection
    {
        return self::query()
            ->select(['id', 'name'])
            ->ordered()
            ->with(['schools:id,name,education_monitor_id'])
            ->get()
            ->map(function (EducationMonitor $monitor): array {
                return [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'schools' => $monitor->schools->only(['id', 'name'])->all(),
                ];
            })->values();
    }

    public static function listWithOfficesAndSchools(): Collection
    {
        return self::query()
            ->select(['id', 'name'])
            ->with([
                'offices:id,name,education_monitor_id',
                'schools:id,name,education_monitor_id',
            ])
            ->ordered()
            ->get()
            ->map(function (EducationMonitor $monitor): array {
                return [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'offices' => $monitor->offices->only(['id', 'name'])->all(),
                    'schools' => $monitor->schools->only(['id', 'name'])->all(),
                ];
            })->values();
    }

    /*
     * End: Custom Functions
     */
}
