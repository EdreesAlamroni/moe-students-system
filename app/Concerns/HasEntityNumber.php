<?php

namespace App\Concerns;

use App\Enums\EntityNumberType;
use App\Services\EntityNumberGenerator;

trait HasEntityNumber
{
    public static function bootHasEntityNumber(): void
    {
        static::creating(function ($model): void {
            if (blank($model->getAttribute('number'))) {
                $model->number = app(EntityNumberGenerator::class)->generate(
                    $model->entityNumberType(),
                );
            }
        });
    }

    abstract public function entityNumberType(): EntityNumberType;
}
