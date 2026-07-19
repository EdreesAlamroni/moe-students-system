<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationServicesOfficeStudentCountByGradeLevelReportUser(EducationServicesOffice $office, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ], $attributes));

    foreach (['report:student-count-by-grade-level:view', 'report:student-count-by-grade-level:print'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
    }

    $user->givePermissionTo([
        'report:student-count-by-grade-level:view',
        'report:student-count-by-grade-level:print',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/reports/student-count-by-grade-level', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the student count by grade level report page', function () {
    $this->get(route('education-services-office.reports.student-count-by-grade-level.index'))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without student count by grade level report permissions cannot view the report', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.reports.student-count-by-grade-level.index'))
        ->assertForbidden();
});

test('authenticated users can visit the student count by grade level report page', function () {
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $user = createEducationServicesOfficeStudentCountByGradeLevelReportUser($office);
    $school = School::factory()->for($monitor, 'monitor')->for($office, 'office')->create();
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $otherGradeLevel = GradeLevel::factory()->create(['name' => 'صف غير مرتبط']);
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.reports.student-count-by-grade-level.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/reports/student-count-by-grade-level')
            ->has('gradeLevels.data', 1)
            ->where('gradeLevels.data.0.uuid', $gradeLevel->uuid)
            ->where('gradeLevels.data.0.name', $gradeLevel->name)
            ->where('gradeLevels.data.0.students_count', 1)
            ->has('educationalStages')
            ->where('can.print', true)
            ->where('filter', [])
            ->missing('gradeLevels.data.1')
        );

    expect(GradeLevel::query()->whereKey($otherGradeLevel->id)->exists())->toBeTrue();
});

test('guests are redirected from the student count by grade level report print page', function () {
    $this->get(route('education-services-office.reports.student-count-by-grade-level.print'))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without print permission cannot print the student count by grade level report', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    Permission::findOrCreate('report:student-count-by-grade-level:view', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $user->givePermissionTo('report:student-count-by-grade-level:view');

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.reports.student-count-by-grade-level.print'))
        ->assertForbidden();
});

test('authenticated users can print the student count by grade level report', function () {
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $user = createEducationServicesOfficeStudentCountByGradeLevelReportUser($office);
    $school = School::factory()->for($monitor, 'monitor')->for($office, 'office')->create();
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.reports.student-count-by-grade-level.print'))
        ->assertOk()
        ->assertViewIs('print.education-services-office.reports.student-count-by-grade-level')
        ->assertSee('إحصائية الطلاب حسب الصفوف الدراسية')
        ->assertSee('الصف الأول')
        ->assertSee('2024-2025')
        ->assertSee($user->name);
});
