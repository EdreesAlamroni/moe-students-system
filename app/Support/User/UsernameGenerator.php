<?php

namespace App\Support\User;

use App\Models\User;

class UsernameGenerator
{
    private const LETTERS = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';

    private const DIGITS = '0123456789';

    private const LETTER_COUNT = 3;

    private const DIGIT_COUNT = 5;

    private const MAX_ATTEMPTS = 25;

    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $username = $this->generateCandidate();

            if (! User::query()->where('username', '=', $username)->exists()) {
                return $username;
            }
        }

        throw new \RuntimeException('Unable to generate a unique username.');
    }

    private function generateCandidate(): string
    {
        $characters = [];

        for ($index = 0; $index < self::LETTER_COUNT; $index++) {
            $characters[] = $this->randomCharacter(self::LETTERS);
        }

        for ($index = 0; $index < self::DIGIT_COUNT; $index++) {
            $characters[] = $this->randomCharacter(self::DIGITS);
        }

        shuffle($characters);

        return implode('', $characters);
    }

    private function randomCharacter(string $alphabet): string
    {
        return $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
}
