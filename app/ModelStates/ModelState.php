<?php

namespace App\ModelStates;

use Spatie\ModelStates\State;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
abstract class ModelState extends State
{
    /**
     * Translation namespace segment under `app.states.*`.
     *
     * Use `{model}.{column}` so models with multiple state columns stay isolated,
     * e.g. `user.state` and `user.request_state`.
     */
    abstract protected static function getTranslationKey(): string;

    abstract public function getUiClasses(): string;

    public function value(): string
    {
        return static::getMorphClass();
    }

    public function label(): string
    {
        return __(sprintf(
            'app.states.%s.labels.%s',
            static::getTranslationKey(),
            static::getMorphClass(),
        ));
    }

    public function action(): ?string
    {
        return __(sprintf(
            'app.states.%s.actions.%s',
            static::getTranslationKey(),
            static::getMorphClass(),
        ));
    }

    /**
     * @return array{value: string, label: string, uiClasses: string, action: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value(),
            'name' => $this->label(),
            'uiClasses' => $this->getUiClasses(),
            'action' => $this->action(),
        ];
    }
}
