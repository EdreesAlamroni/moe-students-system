<?php

namespace App\Support;

use App\Authorization\Administration\EducationMonitorReport;
use App\Authorization\Administration\EducationServicesOfficeReport;
use App\Authorization\Administration\SchoolReport;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\ClassPeriod;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\Administration\AcademicYearPolicy as AdministrationAcademicYearPolicy;
use App\Policies\Administration\ClassPeriodPolicy as AdministrationClassPeriodPolicy;
use App\Policies\Administration\EducationMonitorPolicy as AdministrationEducationMonitorPolicy;
use App\Policies\Administration\EducationMonitorReportPolicy as AdministrationEducationMonitorReportPolicy;
use App\Policies\Administration\EducationServicesOfficePolicy as AdministrationEducationServicesOfficePolicy;
use App\Policies\Administration\EducationServicesOfficeReportPolicy as AdministrationEducationServicesOfficeReportPolicy;
use App\Policies\Administration\GradeLevelPolicy as AdministrationGradeLevelPolicy;
use App\Policies\Administration\SchoolPolicy as AdministrationSchoolPolicy;
use App\Policies\Administration\SchoolReportPolicy as AdministrationSchoolReportPolicy;
use App\Policies\Administration\StudentPolicy as AdministrationStudentPolicy;
use App\Policies\Administration\SubjectPolicy as AdministrationSubjectPolicy;
use App\Policies\Administration\UserPolicy as AdministrationUserPolicy;
use App\Policies\Administration\WarehousePolicy as AdministrationWarehousePolicy;
use App\Policies\EducationMonitor\EducationServicesOfficePolicy as EducationMonitorEducationServicesOfficePolicy;
use App\Policies\EducationMonitor\SchoolPolicy as EducationMonitorSchoolPolicy;
use App\Policies\EducationMonitor\UserPolicy as EducationMonitorUserPolicy;
use App\Policies\EducationServicesOffice\UserPolicy as EducationServicesOfficeUserPolicy;
use App\Policies\School\UserPolicy as SchoolUserPolicy;
use App\Policies\Warehouse\BookDistributionPolicy as WarehouseBookDistributionPolicy;
use App\Policies\Warehouse\EducationMonitorPolicy as WarehouseEducationMonitorPolicy;
use App\Policies\Warehouse\SchoolPolicy as WarehouseSchoolPolicy;
use App\Policies\Warehouse\UserPolicy as WarehouseUserPolicy;
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
            ClassPeriod::class => AdministrationClassPeriodPolicy::class,
            Warehouse::class => AdministrationWarehousePolicy::class,
            EducationMonitor::class => AdministrationEducationMonitorPolicy::class,
            EducationServicesOffice::class => AdministrationEducationServicesOfficePolicy::class,
            School::class => AdministrationSchoolPolicy::class,
            Student::class => AdministrationStudentPolicy::class,
        ],
        'warehouse' => [
            User::class => WarehouseUserPolicy::class,
            EducationMonitor::class => WarehouseEducationMonitorPolicy::class,
            School::class => WarehouseSchoolPolicy::class,
            BookDistribution::class => WarehouseBookDistributionPolicy::class,
        ],
        'education-monitor' => [
            User::class => EducationMonitorUserPolicy::class,
            EducationServicesOffice::class => EducationMonitorEducationServicesOfficePolicy::class,
            School::class => EducationMonitorSchoolPolicy::class,
        ],
        'education-services-office' => [
            User::class => EducationServicesOfficeUserPolicy::class,
        ],
        'school' => [
            User::class => SchoolUserPolicy::class,
        ],
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
        $registered = [];

        foreach (array_keys(self::MODEL_POLICIES) as $group) {
            self::registerGroupPolicies($group, self::MODEL_POLICIES, $registered);
        }

        foreach (array_keys(self::AUTHORIZATION_POLICIES) as $group) {
            self::registerGroupPolicies($group, self::AUTHORIZATION_POLICIES, $registered);
        }
    }

    /**
     * @param  array<string, array<class-string, class-string>>  $policies
     * @param  list<class-string>  $registered
     */
    private static function registerGroupPolicies(string $group, array $policies, array &$registered = []): void
    {
        if (! isset($policies[$group])) {
            throw new InvalidArgumentException("Policy group '{$group}' not found.");
        }

        foreach ($policies[$group] as $subject => $policy) {
            if (in_array($subject, $registered, true)) {
                continue;
            }

            Gate::policy($subject, $policy);

            $registered[] = $subject;
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
