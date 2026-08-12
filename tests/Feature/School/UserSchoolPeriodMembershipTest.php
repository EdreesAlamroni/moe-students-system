<?php

use App\Enums\SchoolAcademicPeriod;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    PolicyRegistrar::registerAll();
});

function createSchoolMembershipAdmin(): User
{
    $user = User::factory()->create();

    foreach ([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
    ]);

    return $user;
}

function createDualPeriodSchool(EducationMonitor $monitor): array
{
    $school = School::factory()->for($monitor, 'monitor')->create();
    $morning = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $evening = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    return compact('school', 'morning', 'evening');
}

test('administration can create a school user for a single-period school automatically', function () {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $schoolPeriod = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::SCHOOL->value,
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'name' => 'Single Period User',
            'username' => 'single.period.user',
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertRedirect();

    $createdUser = User::query()->where('username', 'single.period.user')->firstOrFail();

    expect($createdUser->organization_id)->toBe($schoolPeriod->id)
        ->and($createdUser->schoolPeriods()->pluck('school_periods.id')->all())->toBe([$schoolPeriod->id]);
});

test('administration can assign morning only evening only or both periods', function (array $periodKeys, array $expectedPeriodKeys) {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    ['school' => $school, 'morning' => $morning, 'evening' => $evening] = createDualPeriodSchool($monitor);
    $periods = compact('morning', 'evening');
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $schoolPeriodIds = array_map(
        fn (string $key): int => $periods[$key]->id,
        $periodKeys,
    );

    $username = 'dual.period.'.implode('.', $periodKeys);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::SCHOOL->value,
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'school_period_ids' => $schoolPeriodIds,
            'name' => 'Dual Period User',
            'username' => $username,
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertRedirect();

    $createdUser = User::query()->where('username', $username)->firstOrFail();
    $expectedIds = array_map(
        fn (string $key): int => $periods[$key]->id,
        $expectedPeriodKeys,
    );

    expect($createdUser->schoolPeriods()->pluck('school_periods.id')->sort()->values()->all())
        ->toEqual(collect($expectedIds)->sort()->values()->all())
        ->and($createdUser->organization_id)->toBe($periods[$expectedPeriodKeys[0]]->id);
})->with([
    'morning only' => [['morning'], ['morning']],
    'evening only' => [['evening'], ['evening']],
    'both periods' => [['morning', 'evening'], ['morning', 'evening']],
]);

test('administration rejects school period assignments from different schools', function () {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    ['morning' => $morning] = createDualPeriodSchool($monitor);
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::SCHOOL->value,
            'education_monitor_id' => $monitor->id,
            'school_id' => $morning->school_id,
            'school_period_ids' => [$morning->id, $otherSchoolPeriod->id],
            'name' => 'Invalid Period User',
            'username' => 'invalid.period.user',
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertSessionHasErrors('school_period_ids');
});

test('administration rejects dual-period school users without selected periods', function () {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    ['school' => $school] = createDualPeriodSchool($monitor);
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::SCHOOL->value,
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'school_period_ids' => [],
            'name' => 'Missing Period User',
            'username' => 'missing.period.user',
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertSessionHasErrors('school_period_ids');
});

test('administration requires school period ids when dual-period school omits the field', function () {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    ['school' => $school] = createDualPeriodSchool($monitor);
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::SCHOOL->value,
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'name' => 'Omitted Period User',
            'username' => 'omitted.period.user',
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertSessionHasErrors('school_period_ids');
});

test('updating memberships removes the active period safely', function () {
    $admin = createSchoolMembershipAdmin();
    $monitor = EducationMonitor::factory()->create();
    ['school' => $school, 'morning' => $morning, 'evening' => $evening] = createDualPeriodSchool($monitor);
    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $schoolUser = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $morning->id,
    ]);
    $schoolUser->schoolPeriods()->sync([$morning->id, $evening->id]);

    $this->actingAs($admin, 'administration')
        ->put(route('administration.users.update', ['user' => $schoolUser]), [
            'name' => $schoolUser->name,
            'email' => $schoolUser->email,
            'school_id' => $school->id,
            'school_period_ids' => [$evening->id],
            'roles' => [$role->id],
        ])
        ->assertRedirect();

    $schoolUser->refresh();

    expect($schoolUser->organization_id)->toBe($evening->id)
        ->and($schoolUser->schoolPeriods()->pluck('school_periods.id')->all())->toBe([$evening->id]);
});

test('school users can switch to an assigned period', function () {
    $monitor = EducationMonitor::factory()->create();
    ['morning' => $morning, 'evening' => $evening] = createDualPeriodSchool($monitor);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $morning->id,
    ]);
    $user->schoolPeriods()->sync([$morning->id, $evening->id]);

    Student::factory()->for($morning)->create();
    Student::factory()->for($evening)->create();

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->patch(route('school.period.select'), [
            'school_period_id' => $evening->id,
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($user->refresh()->organization_id)->toBe($evening->id);

    $this->actingAs($user, 'school');

    expect(Student::query()->forCurrentSchool()->count())->toBe(1);
});

test('school users cannot switch to a period they are not assigned to', function () {
    $monitor = EducationMonitor::factory()->create();
    ['morning' => $morning, 'evening' => $evening] = createDualPeriodSchool($monitor);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $morning->id,
    ]);
    $user->schoolPeriods()->sync([$morning->id]);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->patch(route('school.period.select'), [
            'school_period_id' => $evening->id,
        ])
        ->assertSessionHasErrors('school_period_id');

    expect($user->refresh()->organization_id)->toBe($morning->id);
});

test('existing school users keep working after membership backfill', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $user->schoolPeriods()->sync([$schoolPeriod->id]);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertOk();

    expect($user->hasValidOrganizationContext())->toBeTrue();
});

test('school dashboard create attaches membership for the current period', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $manager = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);
    $manager->schoolPeriods()->sync([$schoolPeriod->id]);

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update'] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $manager->givePermissionTo([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
    ]);

    $role = \Spatie\Permission\Models\Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    $this->actingAs($manager, 'school')
        ->post(route('school.users.store'), [
            'name' => 'School Created Member',
            'username' => 'school.created.member',
            'email' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [$role->id],
        ])
        ->assertRedirect();

    $createdUser = User::query()->where('username', 'school.created.member')->firstOrFail();

    expect($createdUser->organization_id)->toBe($schoolPeriod->id)
        ->and($createdUser->schoolPeriods()->pluck('school_periods.id')->all())->toBe([$schoolPeriod->id]);
});

test('multi-period users only see students for the active period', function () {
    $monitor = EducationMonitor::factory()->create();
    ['morning' => $morning, 'evening' => $evening] = createDualPeriodSchool($monitor);

    $morningStudent = Student::factory()->for($morning)->create();
    Student::factory()->for($evening)->create();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $morning->id,
    ]);
    $user->schoolPeriods()->sync([$morning->id, $evening->id]);

    $this->actingAs($user, 'school');

    expect(Student::query()->forCurrentSchool()->pluck('students.id')->all())
        ->toBe([$morningStudent->id]);
});
