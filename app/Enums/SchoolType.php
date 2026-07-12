<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum SchoolType: string
{
    use EnumUtilities;

    case PUBLIC = 'public';
    case PRIVATE = 'private';

    protected function getTranslationKey(): string
    {
        return 'school_types';
    }

    public static function getPluralizedOptions(): Collection
    {
        $collected = collect(self::cases());

        return $collected->map(function (self $case) {
            $translationKey = sprintf(
                'app.enums.%s.plural.%s',
                $case->getTranslationKey(),
                $case->value,
            );

            return [
                'id' => $case->value,
                'name' => __($translationKey),
            ];
        })->values();
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }
}
