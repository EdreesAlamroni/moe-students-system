<?php

namespace App\Support;

use App\Authorization\Administration\EducationMonitorReport;
use App\Authorization\Administration\EducationServicesOfficeReport;
use App\Authorization\Administration\SchoolReport;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Policies\Administration\AcademicYearPolicy as AdministrationAcademicYearPolicy;
use App\Policies\Administration\EducationMonitorPolicy as AdministrationEducationMonitorPolicy;
use App\Policies\Administration\EducationMonitorReportPolicy as AdministrationEducationMonitorReportPolicy;
use App\Policies\Administration\EducationServicesOfficePolicy as AdministrationEducationServicesOfficePolicy;
use App\Policies\Administration\EducationServicesOfficeReportPolicy as AdministrationEducationServicesOfficeReportPolicy;
use App\Policies\Administration\GradeLevelPolicy as AdministrationGradeLevelPolicy;
use App\Policies\Administration\SchoolPolicy as AdministrationSchoolPolicy;
use App\Policies\Administration\SchoolReportPolicy as AdministrationSchoolReportPolicy;
use App\Policies\Administration\SubjectPolicy as AdministrationSubjectPolicy;
use App\Policies\Administration\UserPolicy as AdministrationUserPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class PolicyRegistrar
{
    /**
     * Policies bound to Eloquent models.
     *
     * @var array<string, array<class-string, class-string>>
     */
    private const MODEL_POLICIES = [
        'administration' => [
            User::class => AdministrationUserPolicy::class,
            AcademicYear::class => AdministrationAcademicYearPolicy::class,
            GradeLevel::class => AdministrationGradeLevelPolicy::class,
            Subject::class => AdministrationSubjectPolicy::class,
            EducationMonitor::class => AdministrationEducationMonitorPolicy::class,
            EducationServicesOffice::class => AdministrationEducationServicesOfficePolicy::class,
            School::class => AdministrationSchoolPolicy::class,
        ],
        'warehouse' => [],
        'education-monitor' => [],
        'education-services-office' => [],
        'school' => [],
    ];

    /**
     * Policies bound to non-model authorization resources
     *
     * @var array<string, array<class-string, class-string>>
     */
    private const AUTHORIZATION_POLICIES = [
        'administration' => [
            EducationMonitorReport::class => AdministrationEducationMonitorReportPolicy::class,
            EducationServicesOfficeReport::class => AdministrationEducationServicesOfficeReportPolicy::class,
            SchoolReport::class => AdministrationSchoolReportPolicy::class,
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

        self::registerGroupPolicies($group, self::MODEL_POLICIES);
        self::registerGroupPolicies($group, self::AUTHORIZATION_POLICIES);
    }

    public static function registerAll(): void
    {
        foreach (array_keys(self::MODEL_POLICIES) as $group) {
            self::registerGroupPolicies($group, self::MODEL_POLICIES);
        }

        foreach (array_keys(self::AUTHORIZATION_POLICIES) as $group) {
            self::registerGroupPolicies($group, self::AUTHORIZATION_POLICIES);
        }
    }

    /**
     * @param  array<string, array<class-string, class-string>>  $policies
     */
    private static function registerGroupPolicies(string $group, array $policies): void
    {
        if (! isset($policies[$group])) {
            throw new InvalidArgumentException("Policy group '{$group}' not found.");
        }

        foreach ($policies[$group] as $subject => $policy) {
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
