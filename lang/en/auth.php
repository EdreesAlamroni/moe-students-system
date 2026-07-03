<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'deactivated' => 'This account has been deactivated. Please contact an administrator.',
    'not_approved' => 'This account has not been approved yet.',
    'username_required' => 'The username field is required.',
    'password_required' => 'The password field is required.',

    'pages' => [
        'login' => [
            'title' => 'Sign in to :portal',
            'description' => 'Enter your username and password to access the :portal dashboard.',
        ],
        'forgot_password' => [
            'title' => 'Forgot your password?',
            'description' => 'Enter the email address linked to your :portal account and we will send you a reset link.',
        ],
        'reset_password' => [
            'title' => 'Reset your password',
            'description' => 'Choose a new password for your :portal account.',
        ],
        'confirm_password' => [
            'title' => 'Confirm your password',
            'description' => 'To continue in :portal, please confirm your password.',
        ],
        'change_password' => [
            'title' => 'Change your password',
            'description' => 'You must update your password before accessing the :portal dashboard.',
        ],
    ],

];
