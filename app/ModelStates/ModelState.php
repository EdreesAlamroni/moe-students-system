<?php

namespace App\ModelStates;

use Illuminate\Support\Facades\Lang;
use Spatie\ModelStates\Exceptions\InvalidConfig;
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
        return __($this->translationPath('labels'));
    }

    public function action(): ?string
    {
        $key = $this->translationPath('actions');

        return Lang::has($key) ? __($key) : null;
    }

    /**
     * @return array{id: string, name: string, uiClasses: string, action: string|null}
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

    /**
     * @return array{id: string, name: string, uiClasses: string, action: string|null}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return class-string<static>
     */
    public static function resolve(string $state): string
    {
        $resolved = static::resolveStateClass($state);

        if (! is_subclass_of($resolved, static::class)) {
            throw InvalidConfig::doesNotExtendBaseClass($state, static::class);
        }

        return $resolved;
    }

    protected function translationPath(string $segment): string
    {
        return sprintf(
            'app.states.%s.%s.%s',
            static::getTranslationKey(),
            $segment,
            $this->value(),
        );
    }
}
