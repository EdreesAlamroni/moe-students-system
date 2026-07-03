<?php

namespace App\Support;

use Closure;

class Features
{
    public static function enabled(string $feature): bool
    {
        return boolval(config("features.{$feature}", false));
    }

    public static function excelExportEnabled(): bool
    {
        return self::enabled('excel_export');
    }

    public static function schoolStaffEnabled(): bool
    {
        return self::enabled('school_staff');
    }

    /**
     * Register report Excel export routes only when the feature is enabled.
     */
    public static function registerExcelExportRoutes(Closure $callback): void
    {
        if (! self::excelExportEnabled()) {
            return;
        }

        $callback();
    }

    /**
     * Register school staff routes only when the feature is enabled.
     */
    public static function registerSchoolStaffRoutes(Closure $callback): void
    {
        if (! self::schoolStaffEnabled()) {
            return;
        }

        $callback();
    }
}
