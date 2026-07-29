<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createAdministrationAcademicRecordUser(): User
{
    $user = User::factory()->create();

    foreach (['student:view', 'student:view-academic-record'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-academic-record',
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

test('guests cannot view administration academic records', function () {
    $student = Student::factory()->create();

    $this->get(route('administration.students.academic-record.show', ['student' => $student]))
        ->assertRedirect(route('administration.login'));
});

test('users without permission cannot view administration academic records', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view administration academic records', function () {
    $user = createAdministrationAcademicRecordUser();
    $school = School::factory()->create();

    foreach ([GradeLevelEnum::GRADE_1, GradeLevelEnum::GRADE_2, GradeLevelEnum::GRADE_3] as $gradeLevelEnum) {
        GradeLevel::query()->firstOrCreate(
            ['code' => $gradeLevelEnum->value],
            [
                'name' => $gradeLevelEnum->label(),
                'educational_stage' => $gradeLevelEnum->stage(),
                'order' => $gradeLevelEnum->order(),
            ],
        );
    }

    $gradeLevel = GradeLevel::query()->where('code', GradeLevelEnum::GRADE_3->value)->firstOrFail();
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.academic-record.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/academic-record/show')
            ->where('student.uuid', $student->uuid)
            ->where('requiresAcademicRecord', true)
            ->has('groupedRecords')
        );
});

test('trashed students cannot have their administration academic records viewed', function () {
    $user = createAdministrationAcademicRecordUser();
    $student = Student::factory()->create();
    $student->delete();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.academic-record.show', ['student' => $student]))
        ->assertNotFound();
});
