<?php

use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentPsychosocialCard;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createAdministrationPsychosocialCardUser(): User
{
    $user = User::factory()->create();

    foreach (['student:view', 'student:view-psychosocial-card'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-psychosocial-card',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot view administration psychosocial cards', function () {
    $student = Student::factory()->create();

    $this->get(route('administration.students.psychosocial-card.show', ['student' => $student]))
        ->assertRedirect(route('administration.login'));
});

test('users without permission cannot view administration psychosocial cards', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view administration psychosocial cards', function () {
    $user = createAdministrationPsychosocialCardUser();
    $student = Student::factory()->create();

    StudentPsychosocialCard::factory()->create([
        'student_id' => $student->id,
        'behavioral_problems' => [],
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.psychosocial-card.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/psychosocial-cards/show')
            ->where('student.uuid', $student->uuid)
            ->has('psychosocialCard')
        );
});

test('trashed students cannot have their administration psychosocial cards viewed', function () {
    $user = createAdministrationPsychosocialCardUser();
    $student = Student::factory()->create();
    $student->delete();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.psychosocial-card.show', ['student' => $student]))
        ->assertNotFound();
});
