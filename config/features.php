<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Excel Export
    |--------------------------------------------------------------------------
    |
    | Controls Excel export availability across report modules. When disabled,
    | export routes are not registered and authorization policies deny export.
    |
    */

    'excel_export' => env('FEATURE_EXCEL_EXPORT', false),

    /*
    |--------------------------------------------------------------------------
    | School Staff
    |--------------------------------------------------------------------------
    |
    | Controls school staff management availability. When disabled, staff routes
    | are not registered and authorization policies deny access.
    |
    */

    'school_staff' => env('FEATURE_SCHOOL_STAFF', false),

];
