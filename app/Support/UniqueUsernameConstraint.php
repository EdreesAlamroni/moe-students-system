<?php

namespace App\Support;

use Illuminate\Database\UniqueConstraintViolationException;

final class UniqueUsernameConstraint
{
    public static function isViolation(UniqueConstraintViolationException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'users_username_unique')
            || str_contains($message, 'users.username');
    }

    public static function validationMessage(): string
    {
        return __('validation.unique', [
            'attribute' => __('validation.attributes.username'),
        ]);
    }
}
