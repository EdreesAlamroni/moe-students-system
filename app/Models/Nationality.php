<?php

namespace App\Models;

use App\Concerns\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $fullName
 */
#[Guarded(['id'])]
class Nationality extends Model
{
    /** @use HasFactory<\Database\Factories\NationalityFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    public final const LIBYA_CODE = 'LY';

    /*
     * Start: Accessors & Mutators
     */

    public function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            return sprintf('%s الجنسية', $this->code);
        });
    }

    /*
     * End: Accessors & Mutators
     */

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function libyan(Builder $query): Builder
    {
        return $query->where('code', '=', self::LIBYA_CODE);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Custom Functions
     */

    public static function list(?callable $callback = null, array $additionalColumns = []): Collection
    {
        $columns = array_unique(
            array_merge(['id', 'name', 'code'], $additionalColumns)
        );

        $query = self::query()->select($columns);

        if ($callback) {
            $callback($query);
        }

        return $query
            ->orderByRaw('CASE WHEN code = ? THEN 1 ELSE 0 END DESC', [self::LIBYA_CODE])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->map(function (string $name, int $id): array {
                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->values();
    }

    public function isLibyan(): bool
    {
        return $this->code === self::LIBYA_CODE;
    }

    public static function libyanId(): ?int
    {
        return self::query()->libyan()->value('id');
    }

    /*
     * End: Custom Functions
     */
}
