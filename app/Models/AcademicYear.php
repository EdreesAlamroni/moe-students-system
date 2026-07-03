<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Support\Auth\DashboardAuth;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Guarded(['id'])]
class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicYearFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    private static ?self $current = null;

    public static function current(): ?self
    {
        if (self::$current instanceof self) {
            return self::$current;
        }

        $sessionKey = self::selectedSessionKey();
        $selectedId = $sessionKey ? Session::get($sessionKey) : null;

        if ($selectedId) {
            self::$current = self::query()->find($selectedId);
        }

        if (! self::$current instanceof self) {
            self::$current = self::query()->where('is_active', '=', true)->first();
        }

        return self::$current;
    }

    public static function currentId(): ?int
    {
        return self::current()?->id;
    }

    public static function currentName(): ?string
    {
        return self::current()?->name;
    }

    public static function isCurrentYearActive(): ?bool
    {
        return self::current()?->isActive();
    }

    public static function isCurrentYearInactive(): ?bool
    {
        return ! self::isCurrentYearActive();
    }

    public static function clearCachedCurrent(): void
    {
        self::$current = null;
    }

    public static function createNewYear(array $attributes): self
    {
        $self = DB::transaction(function () use ($attributes) {
            self::query()->where('is_active', '=', true)->update(['is_active' => false]);

            return self::create($attributes);
        });

        // Clear cache to reflect the new active year
        self::clearCachedCurrent();

        return $self;
    }

    /**
     * Returns the per-user session key that stores the selected academic year ID.
     */
    public static function selectedSessionKey(): ?string
    {
        $dashboard = DashboardAuth::resolve();

        $guard = $dashboard?->guard;

        $user = auth($guard)->user();

        return $user ? sprintf('selected_academic_year_id.%d', $user->id) : null;
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', '=', true);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Custom methods
     */

    public function hasAnyRelations(): bool
    {
        return true;
    }

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

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isClosed(): bool
    {
        return ! $this->is_active;
    }

    /*
     * End: Custom methods
     */
}
