<?php

namespace App\Support\User;

class PasswordGenerator
{
    private const DIGITS = '0123456789';

    private const LENGTH = 8;

    public function generate(): string
    {
        $password = '';

        for ($index = 0; $index < self::LENGTH; $index++) {
            $password .= self::DIGITS[random_int(0, strlen(self::DIGITS) - 1)];
        }

        return $password;
    }
}
