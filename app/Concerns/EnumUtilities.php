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

    public function label(): string
    {
        return __(sprintf(
            'app.enums.%s.%s',
            $this->getTranslationKey(),
            $this->value,
        ));
    }

    /**
     * @return array<string, string>
     */
    public function toOption(string $idKey = 'id', string $nameKey = 'name'): array
    {
        return [
            $idKey => $this->value,
            $nameKey => $this->label(),
        ];
    }

    /**
     * @return array{id: string, name: string, key: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->label(),
            'key' => $this->name,
        ];
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    public static function options(string $idKey = 'id', string $nameKey = 'name'): Collection
    {
        return collect(self::cases())->map(function (self $case) use ($idKey, $nameKey): array {
            return $case->toOption($idKey, $nameKey);
        });
    }

    /**
     * @return list<array<string, string>>
     */
    public static function optionsArray(string $idKey = 'id', string $nameKey = 'name'): array
    {
        return self::options($idKey, $nameKey)->values()->all();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array{id: string, name: string, key: string}|array{}
     */
    public static function toArrayFor(self|string $case): array
    {
        if (is_string($case)) {
            $case = self::tryFrom($case);
        }

        return $case?->toArray() ?? [];
    }
}
