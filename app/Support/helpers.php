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
