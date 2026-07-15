<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use Illuminate\Support\Collection;

enum ClassroomDistributionMethod: string
{
    use EnumUtilities;

    case RANDOM = 'random';
    case MANUAL = 'manual';

    protected function getTranslationKey(): string
    {
        return 'classroom_distribution_methods';
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

    public function icon(): string
    {
        return match ($this) {
            self::RANDOM => 'ShuffleIcon',
            self::MANUAL => 'UserPlusIcon',
        };
    }

    public function route(): string
    {
        return match ($this) {
            self::RANDOM => route('school.classroom-distribution.create', ['method' => $this]),
            self::MANUAL => route('school.classroom-distribution.create', ['method' => $this]),
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'route' => $this->route(),
        ];
    }

    public static function buildMethods(): Collection
    {
        return collect(self::cases())->map(function (self $method) {
            return $method->toArray();
        });
    }
}
