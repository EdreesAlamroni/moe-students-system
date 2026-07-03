<?php

namespace App\ModelStates\User\RequestState;

class Rejected extends UserRequestState
{
    protected static string $name = 'rejected';

    public function getUiClasses(): string
    {
        return 'pill pill-red';
    }
}
