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
    abstract public function getUiClasses(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([Approved::class, Rejected::class, Pending::class])
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
