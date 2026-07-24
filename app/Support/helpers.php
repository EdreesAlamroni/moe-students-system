<?php

use Illuminate\Support\Facades\Lang;

if (! function_exists('flash_success')) {
    /**
     * Flash a translated success message with replacements.
     */
    function flash_success(string $key, array $replacements = []): void
    {
        $key = sprintf('alerts.messages.%s', $key);

        $message = Lang::get($key, $replacements);

        flash()->success($message);
    }
}

if (! function_exists('flash_error')) {
    /**
     * Flash a translated error message with replacements.
     */
    function flash_error(string $key, array $replacements = []): void
    {
        $key = sprintf('alerts.messages.%s', $key);

        $message = Lang::get($key, $replacements);

        flash()->error($message);
    }
}

if (! function_exists('classroom_names')) {
    /**
     * Get the classroom names.
     */
    function classroom_names(): array
    {
        return collect(
            array_map('strval', range(1, 12))
        )->map(function (string $name): array {
            return [
                'key' => $name,
                'id' => $name,
                'name' => $name,
            ];
        })->all();
    }
}
