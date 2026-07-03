<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (blank($model->getAttribute('uuid'))) {
                $model->uuid = Str::uuid7()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    #[Scope]
    protected function whereUuid(Builder $query, string $uuid): Builder
    {
        return $query->where('uuid', '=', $uuid);
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::where('uuid', '=', $uuid)->first();
    }
}
