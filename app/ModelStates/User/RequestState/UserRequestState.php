<?php

namespace App\ModelStates\User\RequestState;

use App\Models\User;
use App\ModelStates\ModelState;
use Spatie\ModelStates\StateConfig;

/**
 * @extends ModelState<User>
 */
abstract class UserRequestState extends ModelState
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Approved::class)
            ->allowTransition(Pending::class, Rejected::class)
            ->allowTransition(Rejected::class, Approved::class);
    }

    protected static function getTranslationKey(): string
    {
        return 'user.request_state';
    }
}
