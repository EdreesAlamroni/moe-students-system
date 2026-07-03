<?php

namespace App\Enums;

enum AuthPage: string
{
    case LOGIN = 'login';
    case FORGOT_PASSWORD = 'forgot_password';
    case RESET_PASSWORD = 'reset_password';
    case CONFIRM_PASSWORD = 'confirm_password';
    case CHANGE_PASSWORD = 'change_password';
}
