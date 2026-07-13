<?php

namespace App\Authorization\Administration;

use App\Authorization\Contracts\AuthorizationResource;

/**
 * Authorization target for the education services offices report.
 *
 * This is not an Eloquent model; it is only used as a policy identifier
 * and is never instantiated.
 */
final class EducationServicesOfficeReport implements AuthorizationResource
{
    private function __construct() {}
}
