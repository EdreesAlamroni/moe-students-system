<?php

namespace App\Authorization\Administration;

use App\Authorization\Contracts\AuthorizationResource;

/**
 * Authorization target for the education monitors report.
 *
 * This is not an Eloquent model; it is only used as a policy identifier
 * and is never instantiated.
 */
final class EducationMonitorReport implements AuthorizationResource
{
    private function __construct() {}
}
