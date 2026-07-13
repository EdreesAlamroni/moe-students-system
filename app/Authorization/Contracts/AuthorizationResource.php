<?php

namespace App\Authorization\Contracts;

/**
 * Marks a class as a non-model authorization resource.
 *
 * Implementing classes are not Eloquent models. They act as stable,
 * type-safe identifiers that map to a policy so features without an
 * underlying model (reports, dashboards, exports, ...) can be guarded
 * through the Gate, e.g.:
 *
 *   Gate::authorize('view', EducationMonitorReport::class);
 *   Gate::authorize('print', EducationMonitorReport::class);
 */
interface AuthorizationResource {}
