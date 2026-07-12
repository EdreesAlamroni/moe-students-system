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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $education_monitor_id
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
 * @property-read EducationMonitor $monitor
 * @property-read int|null $schools_count
 * @property-read int|null $students_count
 */
#[Guarded(['id'])]
class EducationServicesOffice extends Model
{
    /** @use HasFactory<\Database\Factories\EducationServicesOfficeFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'education_monitor_id' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
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
    protected function forCurrentEducationMonitor(Builder $query): Builder
    {
        $id = auth('education_monitor')->user()->model_id;

        if (is_null($id)) {
            return $query;
        }

        return $query->where('education_monitor_id', '=', $id);
    }

    #[Scope]
    protected function ordered(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    #[Scope]
    protected function orderedByMonitor(Builder $query, string $direction = 'asc'): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->join('education_monitors', 'education_monitors.id', '=', "{$table}.education_monitor_id")
            ->orderBy('education_monitors.name', $direction)
            ->orderBy("{$table}.name", $direction);
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

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, School::class);
    }

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

    public function nameWithMonitor(): string
    {
        $this->loadMissing(['monitor:id,name']);

        return "{$this->monitor->name} - {$this->name}";
    }

    public function printOrganizationLines(): array
    {
        $this->loadMissing(['monitor:id,name']);

        return [$this->monitor->name, $this->name];
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

    public static function listWithSchools(): Collection
    {
        return self::query()
            ->select(['id', 'name'])
            ->with(['schools:id,name,education_services_office_id'])
            ->get()
            ->map(function (EducationServicesOffice $office): array {
                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'schools' => $office->schools->only(['id', 'name'])->all(),
                ];
            })->values();
    }

    /*
     * End: Custom Functions
     */
}
