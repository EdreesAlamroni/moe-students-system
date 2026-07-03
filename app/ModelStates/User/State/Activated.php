<?php

namespace App\ModelStates\User\State;

class Activated extends UserState
{
    protected static string $name = 'activated';

    public function getUiClasses(): string
    {
        return 'pill pill-green';
    }
}
