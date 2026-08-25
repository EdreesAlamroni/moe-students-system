<?php

namespace App\ModelStates\User\State;

use App\Models\User;
use App\ModelStates\ModelState;
use Spatie\ModelStates\StateConfig;

/**
 * @extends ModelState<User>
 */
abstract class UserState extends ModelState
{
    abstract public function getUiClasses(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([Activated::class, Deactivated::class])
            ->default(Activated::class)
            ->allowTransition(Activated::class, Deactivated::class)
            ->allowTransition(Deactivated::class, Activated::class);
    }

    protected static function getTranslationKey(): string
    {
        return 'user.state';
    }
}
