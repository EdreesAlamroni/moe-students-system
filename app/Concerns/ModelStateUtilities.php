<?php

namespace App\Concerns;

use App\ModelStates\ModelState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\State;

/**
 * @mixin Model
 * @mixin HasStates
 */
trait ModelStateUtilities
{
    /**
     * UI-ready options for every registered state on the given column.
     */
    public static function getStateOptions(string $column = 'state'): Collection
    {
        /** @var static $model */
        $model = static::query()->getModel();

        return collect(static::getStatesFor($column))
            ->map(function (string $state) use ($model, $column): ?array {
                return $model->getStateOption($column, $state);
            })
            ->filter(fn (?array $option): bool => $option !== null)
            ->values();
    }

    /**
     * UI-ready options for states that can be transitioned to from the current state.
     *
     * Wraps Spatie's {@see State::transitionableStates()} and formats each via {@see getStateOption()}.
     */
    public function getTransitionableStates(string $column = 'state'): Collection
    {
        $current = $this->{$column};

        if (! $current instanceof State) {
            return new Collection;
        }

        return collect($current->transitionableStates())
            ->map(function (string $state) use ($column): ?array {
                return $this->getStateOption($column, $state);
            })
            ->filter(fn (?array $option): bool => $option !== null)
            ->values();
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     uiClasses: string,
     *     action: string|null
     * }|null
     */
    protected function getStateOption(string $column, string $state): ?array
    {
        $stateClass = $this->getStateClassFor($column);

        if ($stateClass === null) {
            return null;
        }

        $instance = $stateClass::make($state, $this);

        return $instance instanceof ModelState ? $instance->toArray() : null;
    }

    /**
     * @return class-string<State>|null
     */
    protected function getStateClassFor(string $column): ?string
    {
        $cast = $this->getCasts()[$column] ?? null;

        return is_subclass_of($cast, State::class) ? $cast : null;
    }
}
