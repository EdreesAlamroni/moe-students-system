<?php

namespace App\Concerns;

use BackedEnum;
use Illuminate\Support\Collection;

/**
 * @mixin BackedEnum
 */
trait EnumUtilities
{
    abstract protected function getTranslationKey(): string;

    public function id(): string|int
    {
        return $this->value;
    }

    public function label(): string
    {
        return __(sprintf(
            'app.enums.%s.%s',
            $this->getTranslationKey(),
            $this->value,
        ));
    }

    public function name(): string
    {
        return $this->label();
    }

    public function toOption(string $idKey = 'id', string $nameKey = 'name'): array
    {
        return [
            $idKey => $this->value,
            $nameKey => $this->label(),
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->label(),
            'key' => $this->name,
        ];
    }

    public static function options(string $idKey = 'id', string $nameKey = 'name'): Collection
    {
        return collect(self::cases())->map(function (self $case) use ($idKey, $nameKey): array {
            return $case->toOption($idKey, $nameKey);
        });
    }

    public static function optionsArray(string $idKey = 'id', string $nameKey = 'name'): array
    {
        return self::options($idKey, $nameKey)->values()->all();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArrayFor(self|string|int $case): array
    {
        if (is_string($case) || is_int($case)) {
            $case = self::tryFrom($case);
        }

        return $case?->toArray() ?? [];
    }
}
