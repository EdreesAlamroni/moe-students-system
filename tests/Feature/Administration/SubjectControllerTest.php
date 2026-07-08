<?php

use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

function createSubjectAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['subject:view-any', 'subject:view', 'subject:create', 'subject:update', 'subject:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'subject:view-any',
        'subject:view',
        'subject:create',
        'subject:update',
        'subject:delete',
    ]);

    return $user;
}

function subjectPayload(GradeLevel $gradeLevel, array $overrides = []): array
{
    return array_merge([
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Mathematics',
        'code' => 'MATH-101',
        'included_in_total_score' => true,
        'needs_lab' => false,
        'description' => null,
    ], $overrides);
}

beforeEach(function () {
    AcademicYear::clearCachedCurrent();
    PolicyRegistrar::register(Request::create('/administration/subjects', 'GET'));
});

test('guests are redirected from the subjects page', function () {
    $this->get(route('administration.subjects.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without subject permissions cannot view subjects', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.index'))
        ->assertForbidden();
});

test('authenticated users can visit the subjects page', function () {
    $user = createSubjectAdminUser();
    $subject = Subject::factory()->create(['name' => 'Science', 'code' => 'SCI-101']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/subjects/index')
            ->has('subjects.data', 1)
            ->where('subjects.data.0.name', $subject->name)
            ->where('filter', [])
        );
});

test('subjects page can be filtered by name', function () {
    $user = createSubjectAdminUser();
    Subject::factory()->create(['name' => 'Mathematics', 'code' => 'MATH-101']);
    Subject::factory()->create(['name' => 'Science', 'code' => 'SCI-101']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.index', ['filter' => ['name' => 'Math']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('subjects.data', 1)
            ->where('subjects.data.0.name', 'Mathematics')
            ->where('filter.name', 'Math')
        );
});

test('authenticated users can visit the create subject page', function () {
    $user = createSubjectAdminUser();
    GradeLevel::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/subjects/create')
            ->has('gradeLevels')
        );
});

test('authenticated users can store a subject', function () {
    $user = createSubjectAdminUser();
    $gradeLevel = GradeLevel::factory()->create();
    $payload = subjectPayload($gradeLevel);

    $this->actingAs($user, 'administration')
        ->post(route('administration.subjects.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'code' => $payload['code'],
        'name' => $payload['name'],
        'grade_level_id' => $gradeLevel->id,
    ]);
});

test('store validates the subject code', function () {
    $user = createSubjectAdminUser();
    $gradeLevel = GradeLevel::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.subjects.store'), subjectPayload($gradeLevel, ['code' => 'invalid code']))
        ->assertSessionHasErrors('code');
});

test('authenticated users can visit the show subject page', function () {
    $user = createSubjectAdminUser();
    $subject = Subject::factory()->create(['name' => 'Physics', 'code' => 'PHY-101']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.show', ['subject' => $subject]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/subjects/show')
            ->where('subject.name', $subject->name)
            ->where('subject.code', $subject->code)
        );
});

test('authenticated users can visit the edit subject page', function () {
    $user = createSubjectAdminUser();
    $subject = Subject::factory()->create(['name' => 'Physics', 'code' => 'PHY-101']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.subjects.edit', ['subject' => $subject]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/subjects/edit')
            ->where('subject.name', $subject->name)
            ->has('gradeLevels')
        );
});

test('authenticated users can update a subject', function () {
    $user = createSubjectAdminUser();
    $gradeLevel = GradeLevel::factory()->create();
    $subject = Subject::factory()->create([
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Physics',
        'code' => 'PHY-101',
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.subjects.update', ['subject' => $subject]), subjectPayload($gradeLevel, [
            'name' => 'Advanced Physics',
            'code' => 'PHY-201',
        ]))
        ->assertRedirect(route('administration.subjects.show', ['subject' => $subject]));

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Advanced Physics',
        'code' => 'PHY-201',
    ]);
});

test('authenticated users can delete a subject', function () {
    $user = createSubjectAdminUser();
    $subject = Subject::factory()->create();

    $this->actingAs($user, 'administration')
        ->delete(route('administration.subjects.destroy', ['subject' => $subject]))
        ->assertRedirect(route('administration.subjects.index'));

    $this->assertSoftDeleted($subject);
});
