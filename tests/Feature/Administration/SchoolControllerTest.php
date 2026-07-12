<?php

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

function createSchoolAdminUser(): User
{
    $user = User::factory()->create();

    $permissions = [
        'school:view-any',
        'school:view',
        'school:create',
        'school:update',
        'school:delete',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function publicSchoolPayload(EducationMonitor $monitor, array $overrides = []): array
{
    return array_merge([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => null,
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'name' => 'مدرسة الشهداء',
        'students_gender' => SchoolStudentsGender::MIXED->value,
        'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/schools', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the schools page', function () {
    $this->get(route('administration.schools.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without school permissions cannot view schools', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index'))
        ->assertForbidden();
});

test('authenticated users can visit the schools index page', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.uuid', $school->uuid)
            ->where('schools.data.0.monitor.name', $monitor->name)
            ->has('monitors')
            ->has('types')
        );
});

test('authenticated users can filter schools by education monitor', function () {
    $user = createSchoolAdminUser();
    $monitorA = EducationMonitor::factory()->create();
    $monitorB = EducationMonitor::factory()->create();

    School::factory()->for($monitorA, 'monitor')->create(['name' => 'مدرسة أ']);
    School::factory()->for($monitorB, 'monitor')->create(['name' => 'مدرسة ب']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index', ['filter' => ['education_monitor_id' => $monitorA->id]]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('schools.data', 1)
            ->where('schools.data.0.monitor.name', $monitorA->name)
        );
});

test('authenticated users can visit the create school page', function () {
    $user = createSchoolAdminUser();
    EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/create')
            ->has('monitors')
            ->has('types')
            ->has('academicPeriods')
            ->has('studentsGender')
            ->where('schoolPrivateType', SchoolType::PRIVATE->value)
            ->where('schoolDualAcademicPeriod', SchoolAcademicPeriod::DUAL_PERIOD->value)
        );
});

test('authenticated users can store a public single-period school', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor))
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);

    $school = School::query()->firstOrFail();

    expect($school->education_monitor_id)->toBe($monitor->id)
        ->and($school->type)->toBe(SchoolType::PUBLIC)
        ->and($school->academic_period)->toBe(SchoolAcademicPeriod::MORNING);

    $this->assertDatabaseHas('school_educational_stages', [
        'school_id' => $school->id,
        'stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION->value,
    ]);
});

test('authenticated users can store a dual-period school as two records', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $payload = [
        'education_monitor_id' => $monitor->id,
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
        'name_morning' => 'مدرسة الصباح',
        'name_evening' => 'مدرسة المساء',
        'students_gender_morning' => SchoolStudentsGender::BOYS->value,
        'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
        'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
        'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 2);

    $this->assertDatabaseHas('schools', [
        'education_monitor_id' => $monitor->id,
        'name' => 'مدرسة الصباح',
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'students_gender' => SchoolStudentsGender::BOYS->value,
    ]);
    $this->assertDatabaseHas('schools', [
        'education_monitor_id' => $monitor->id,
        'name' => 'مدرسة المساء',
        'academic_period' => SchoolAcademicPeriod::EVENING->value,
        'students_gender' => SchoolStudentsGender::GIRLS->value,
    ]);

    expect(SchoolEducationalStage::query()->count())->toBe(2);
});

test('education services office is required when the monitor has offices', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'education_services_office_id' => null,
        ]))
        ->assertSessionHasErrors('education_services_office_id');
});

test('private school requires company name and branch and building types', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'type' => SchoolType::PRIVATE->value,
        ]))
        ->assertSessionHasErrors(['educational_company_name', 'branch_type', 'building_type']);
});

test('authenticated users can visit the show school page', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/show')
            ->where('school.uuid', $school->uuid)
            ->where('school.serial_number', $school->serial_number)
        );
});

test('authenticated users can visit the edit school page', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.edit', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/edit')
            ->where('school.uuid', $school->uuid)
            ->has('branchTypes')
            ->has('buildingTypes')
        );
});

test('authenticated users can update the school name for a public school', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create([
        'type' => SchoolType::PUBLIC->value,
        'name' => 'الاسم القديم',
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), [
            'name' => 'الاسم الجديد',
        ])
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    expect($school->refresh()->name)->toBe('الاسم الجديد');
});

test('authenticated users can update private school specific fields', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create([
        'type' => SchoolType::PRIVATE->value,
        'educational_company_name' => 'الشركة القديمة',
        'branch_type' => SchoolBranchType::MAIN->value,
        'building_type' => SchoolBuildingType::SCHOOL->value,
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), [
            'name' => $school->name,
            'educational_company_name' => 'الشركة الجديدة',
            'branch_type' => SchoolBranchType::SUB->value,
            'building_type' => SchoolBuildingType::VILLA->value,
        ])
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    $school->refresh();

    expect($school->educational_company_name)->toBe('الشركة الجديدة')
        ->and($school->branch_type)->toBe(SchoolBranchType::SUB)
        ->and($school->building_type)->toBe(SchoolBuildingType::VILLA);
});

test('authenticated users can delete a school', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create();

    $this->actingAs($user, 'administration')
        ->delete(route('administration.schools.destroy', ['school' => $school]))
        ->assertRedirect(route('administration.schools.index'));

    $this->assertSoftDeleted($school);
});
