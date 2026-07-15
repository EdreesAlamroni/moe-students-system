<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum ClassroomDistributionResetScope: string
{
    use EnumUtilities;

    case ALL = 'all';
    case SELECTED = 'selected';

    protected function getTranslationKey(): string
    {
        return 'classroom_distribution_reset_scopes';
    }

    public function description(): string
    {
        $key = sprintf(
            'app.enums.%s.description.%s',
            $this->getTranslationKey(),
            $this->value,
        );

        return __($key);
    }

    public function requiresGradeLevelSelection(): bool
    {
        return $this === self::SELECTED;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->label(),
            'description' => $this->description(),
        ];
    }

    public static function buildScopes(): Collection
    {
        return collect(self::cases())->map(function (self $scope) {
            return $scope->toArray();
        });
    }
}
