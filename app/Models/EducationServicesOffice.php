<?php

namespace App\Models;

use App\Concerns\HasEntityNumber;
use App\Concerns\HasUuid;
use App\Enums\EntityNumberType;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
 * @property string $number
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
 * @property-read EloquentCollection<int, School> $schools
 * @property-read EloquentCollection<int, SchoolPeriod> $schoolPeriods
 * @property-read int|null $schools_count
 * @property-read int|null $students_count
 */
#[Guarded(['id', 'number'])]
class EducationServicesOffice extends Model
{
    /** @use HasFactory<\Database\Factories\EducationServicesOfficeFactory> */
    use HasEntityNumber, HasFactory, HasUuid, SoftDeletes;

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
        $id = auth('education_monitor')->user()->organization_id;

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
        return $this->morphMany(User::class, 'organization');
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(EducationMonitor::class, 'education_monitor_id');
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function schoolPeriods(): HasMany
    {
        return $this->hasMany(SchoolPeriod::class);
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            SchoolPeriod::class,
            'education_services_office_id',
            'school_period_id',
        );
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function entityNumberType(): EntityNumberType
    {
        return EntityNumberType::EducationServicesOffice;
    }

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
            ->with(['schools.periods:id,school_id,name,academic_period'])
            ->get()
            ->map(function (EducationServicesOffice $office): array {
                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'schools' => $office->schools
                        ->map(function (School $school): array {
                            return [
                                'id' => $school->id,
                                'name' => $school->name,
                                'periods' => $school->periods
                                    ->sortBy(fn (SchoolPeriod $period): int => $period->academic_period->isMorning() ? 0 : 1)
                                    ->values()
                                    ->map(function (SchoolPeriod $period): array {
                                        return [
                                            'id' => $period->id,
                                            'name' => $period->display_name,
                                            'academic_period' => $period->academic_period->toArray(),
                                        ];
                                    })->all(),
                            ];
                        })->values()->all(),
                ];
            })->values();
    }

    public function organizationUsers(): Collection
    {
        return $this->users()->orderBy('id')->get();
    }

    /*
     * End: Custom Functions
     */
}
