<?php

use App\Enums\SchoolAcademicPeriod;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

function createClassPeriodAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['class-period:view-any', 'class-period:view', 'class-period:create', 'class-period:update', 'class-period:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'class-period:view-any',
        'class-period:view',
        'class-period:create',
        'class-period:update',
        'class-period:delete',
    ]);

    return $user;
}

function classPeriodPayload(array $overrides = []): array
{
    return array_merge([
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'name' => 'الحصة الأولى',
        'start_time' => '08:00',
        'end_time' => '08:45',
        'order' => 1,
        'is_break' => false,
    ], $overrides);
}

beforeEach(function () {
    AcademicYear::clearCachedCurrent();
    AcademicYear::factory()->active()->create();
    PolicyRegistrar::register(Request::create('/administration/class-periods', 'GET'));
});

test('guests are redirected from the class periods page', function () {
    $this->get(route('administration.class-periods.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without class period permissions cannot view class periods', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.index'))
        ->assertForbidden();
});

test('authenticated users can visit the class periods page', function () {
    $user = createClassPeriodAdminUser();
    $classPeriod = ClassPeriod::factory()->create(['name' => 'الحصة الأولى']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/class-periods/index')
            ->has('classPeriods.data', 1)
            ->where('classPeriods.data.0.name', $classPeriod->name)
            ->has('academicPeriods')
            ->where('filter', [])
        );
});

test('class periods page can be filtered by name', function () {
    $user = createClassPeriodAdminUser();
    ClassPeriod::factory()->create(['name' => 'الحصة الأولى', 'order' => 1]);
    ClassPeriod::factory()->create(['name' => 'الحصة الثانية', 'order' => 2]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.index', ['filter' => ['name' => 'الأولى']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('classPeriods.data', 1)
            ->where('classPeriods.data.0.name', 'الحصة الأولى')
            ->where('filter.name', 'الأولى')
        );
});

test('class periods page can be filtered by academic period', function () {
    $user = createClassPeriodAdminUser();
    ClassPeriod::factory()->create([
        'name' => 'الحصة الصباحية',
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 1,
    ]);
    ClassPeriod::factory()->create([
        'name' => 'الحصة المسائية',
        'academic_period' => SchoolAcademicPeriod::EVENING,
        'order' => 1,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.index', [
            'filter' => ['academic_period' => SchoolAcademicPeriod::EVENING->value],
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('classPeriods.data', 1)
            ->where('classPeriods.data.0.name', 'الحصة المسائية')
            ->where('filter.academic_period', SchoolAcademicPeriod::EVENING->value)
        );
});

test('authenticated users can visit the create class period page', function () {
    $user = createClassPeriodAdminUser();

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.create', [
            'academicPeriod' => SchoolAcademicPeriod::MORNING->value,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/class-periods/create')
            ->where('academicPeriod.id', SchoolAcademicPeriod::MORNING->value)
            ->where('nextOrder', 1)
        );
});

test('authenticated users can store a class period', function () {
    $user = createClassPeriodAdminUser();
    $payload = classPeriodPayload();

    $this->actingAs($user, 'administration')
        ->post(route('administration.class-periods.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('class_periods', [
        'name' => $payload['name'],
        'academic_period' => $payload['academic_period'],
        'order' => $payload['order'],
        'is_break' => false,
    ]);
});

test('store validates the end time is after the start time', function () {
    $user = createClassPeriodAdminUser();

    $this->actingAs($user, 'administration')
        ->post(route('administration.class-periods.store'), classPeriodPayload([
            'start_time' => '09:00',
            'end_time' => '08:00',
        ]))
        ->assertSessionHasErrors('end_time');
});

test('authenticated users can visit the show class period page', function () {
    $user = createClassPeriodAdminUser();
    $classPeriod = ClassPeriod::factory()->create(['name' => 'الحصة الأولى']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.show', ['classPeriod' => $classPeriod]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/class-periods/show')
            ->where('classPeriod.name', $classPeriod->name)
            ->where('classPeriod.academic_period.id', SchoolAcademicPeriod::MORNING->value)
        );
});

test('authenticated users can visit the edit class period page', function () {
    $user = createClassPeriodAdminUser();
    $classPeriod = ClassPeriod::factory()->create(['name' => 'الحصة الأولى']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.class-periods.edit', ['classPeriod' => $classPeriod]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/class-periods/edit')
            ->where('classPeriod.name', $classPeriod->name)
            ->has('academicPeriods')
        );
});

test('authenticated users can update a class period', function () {
    $user = createClassPeriodAdminUser();
    $classPeriod = ClassPeriod::factory()->create([
        'name' => 'الحصة الأولى',
        'start_time' => '08:00',
        'end_time' => '08:45',
        'order' => 1,
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.class-periods.update', ['classPeriod' => $classPeriod]), classPeriodPayload([
            'name' => 'الحصة المعدلة',
            'start_time' => '08:15',
            'end_time' => '09:00',
            'order' => 1,
        ]))
        ->assertRedirect(route('administration.class-periods.show', ['classPeriod' => $classPeriod]));

    $this->assertDatabaseHas('class_periods', [
        'id' => $classPeriod->id,
        'name' => 'الحصة المعدلة',
    ]);
});

test('authenticated users can delete a class period', function () {
    $user = createClassPeriodAdminUser();
    $classPeriod = ClassPeriod::factory()->create();

    $this->actingAs($user, 'administration')
        ->delete(route('administration.class-periods.destroy', ['classPeriod' => $classPeriod]))
        ->assertRedirect(route('administration.class-periods.index'));

    $this->assertSoftDeleted($classPeriod);
});
