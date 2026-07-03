<?php

namespace App\ModelStates\User\RequestState;

class Pending extends UserRequestState
{
    protected static string $name = 'pending';

    public function getUiClasses(): string
    {
        return 'pill pill-yellow';
    }
}
