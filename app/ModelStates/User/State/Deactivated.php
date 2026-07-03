<?php

namespace App\ModelStates\User\State;

class Deactivated extends UserState
{
    protected static string $name = 'deactivated';

    public function getUiClasses(): string
    {
        return 'pill pill-red';
    }
}
