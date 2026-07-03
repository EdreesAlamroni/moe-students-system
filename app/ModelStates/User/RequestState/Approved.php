<?php

namespace App\ModelStates\User\RequestState;

class Approved extends UserRequestState
{
    protected static string $name = 'approved';

    public function getUiClasses(): string
    {
        return 'pill pill-green';
    }
}
