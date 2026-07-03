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
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Activated::class)
            ->allowTransition(Activated::class, Deactivated::class)
            ->allowTransition(Deactivated::class, Activated::class);
    }

    protected static function getTranslationKey(): string
    {
        return 'user.state';
    }
}
