<?php

namespace App\Services;

use App\Enums\EntityNumberType;
use Illuminate\Support\Facades\DB;

class EntityNumberGenerator
{
    public function generate(EntityNumberType $type): string
    {
        $sequence = (int) DB::selectOne(
            sprintf("SELECT nextval('%s') AS seq", $type->sequenceName())
        )->seq;

        return $type->format($sequence);
    }
}
