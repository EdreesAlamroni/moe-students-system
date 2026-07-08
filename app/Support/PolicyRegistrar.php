<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\User;
use App\Policies\Administration\AcademicYearPolicy as AdministrationAcademicYearPolicy;
use App\Policies\Administration\GradeLevelPolicy as AdministrationGradeLevelPolicy;
use App\Policies\Administration\SubjectPolicy as AdministrationSubjectPolicy;
use App\Policies\Administration\UserPolicy as AdministrationUserPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class PolicyRegistrar
{
    /** @var array<string, array<class-string, class-string>> */
    private const MODEL_POLICIES = [
        'administration' => [
            User::class => AdministrationUserPolicy::class,
            AcademicYear::class => AdministrationAcademicYearPolicy::class,
            GradeLevel::class => AdministrationGradeLevelPolicy::class,
            Subject::class => AdministrationSubjectPolicy::class,
        ],
        'warehouse' => [],
        'education-monitor' => [],
        'education-services-office' => [],
        'school' => [],
    ];

    public static function register(Request $request): void
    {
        $group = self::group($request);

        if (is_null($group)) {
            return;
        }

        if (! isset(self::MODEL_POLICIES[$group])) {
            throw new InvalidArgumentException("Policy group '{$group}' not found.");
        }

        $policies = self::MODEL_POLICIES[$group];

        foreach ($policies as $subject => $policy) {
            Gate::policy($subject, $policy);
        }
    }

    private static function group(Request $request): ?string
    {
        $segment = $request->segment(1);

        return match ($segment) {
            'administration' => 'administration',
            'warehouse' => 'warehouse',
            'education-monitor' => 'education-monitor',
            'education-services-office' => 'education-services-office',
            'school' => 'school',
            default => null,
        };
    }
}
