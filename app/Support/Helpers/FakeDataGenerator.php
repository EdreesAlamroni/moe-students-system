<?php

namespace App\Support\Helpers;

use Faker\Generator;
use Illuminate\Support\Str;

/**
 * Utility class for generating realistic fake data for testing.
 */
final class FakeDataGenerator
{
    private const LIBYAN_MOBILE_PREFIXES = ['091', '092', '093', '094', '095'];

    /**
     * Generate a random Libyan mobile number in local format.
     *
     * Example: "0911234567"
     */
    public static function libyanMobile(Generator $faker): string
    {
        $prefix = $faker->randomElement(self::LIBYAN_MOBILE_PREFIXES);
        $number = $faker->numerify('#######');

        return Str::of($prefix)->append($number)->toString();
    }

    /**
     * Generate a random Libyan WhatsApp number in E.164 format.
     *
     * Example: "+218911234567"
     */
    public static function libyanWhatsapp(Generator $faker): string
    {
        $local = self::libyanMobile($faker);
        $number = ltrim($local, '0');

        return Str::of('+218')->append($number)->toString();
    }

    /**
     * Generate a valid Facebook profile or page URL.
     */
    public static function facebookUrl(Generator $faker): string
    {
        return $faker->boolean
            ? Str::of('https://www.facebook.com/profile.php?id=')->append((string) $faker->numberBetween(1000000000, 9999999999))->toString()
            : Str::of('https://www.facebook.com/')->append(self::facebookUsername($faker))->toString();
    }

    /**
     * Generate a valid Facebook username (at least 5 characters, no consecutive dots or underscores).
     */
    public static function facebookUsername(Generator $faker): string
    {
        $base = $faker->userName();

        $username = preg_replace('/[._]{2,}/', '', $base);
        $username = preg_replace('/^[^A-Za-z0-9]+/', '', $username);
        $username = preg_replace('/[^A-Za-z0-9._]/', '', $username);
        $username = substr($username, 0, 30);

        if (strlen($username) < 5) {
            $username .= $faker->randomNumber(5);
        }

        return $username;
    }

    /**
     * Generate a valid Libyan national ID number.
     *
     * The Libyan national ID is a 12-digit number. The first digit indicates
     * gender (1 for male, 2 for female), followed by the 4-digit year of
     * birth, and then a 7-digit random number.
     *
     * If gender or yearOfBirth are not provided, they will be generated
     * automatically.
     *
     * @param  string|null  $gender  The gender ('male' or 'female').
     * @param  string|null  $yearOfBirth  The four-digit year of birth.
     */
    public static function libyanNationalId(Generator $faker, ?string $gender = null, ?string $yearOfBirth = null): string
    {
        $gender ??= $faker->randomElement(['male', 'female']);
        $yearOfBirth ??= $faker->dateTimeBetween('-30 years', '-18 years')->format('Y');

        $prefix = match (strtolower($gender)) {
            'male', 'm', '1' => '1',
            default => '2',
        };

        $suffix = $faker->numerify('#######');

        return Str::of($prefix)
            ->append($yearOfBirth)
            ->append($suffix)
            ->toString();
    }
}
