<?php

namespace App\Authorization\Administration;

use App\Authorization\Contracts\AuthorizationResource;

/**
 * Authorization target for the schools report.
 *
 * This is not an Eloquent model; it is only used as a policy identifier
 * and is never instantiated.
 */
final class SchoolReport implements AuthorizationResource
{
    private function __construct() {}
}
