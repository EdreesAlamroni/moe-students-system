<?php

use App\Authorization\Administration\EducationMonitorReport;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Municipal;
use App\Models\User;
use App\Policies\Administration\EducationMonitorReportPolicy;
use App\Support\ModelAbilityMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps individual abilities with can', function () {
    $municipal = Municipal::factory()->makeOne();

    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('view', fn () => true);
    Gate::define('update', fn () => false);

    expect(ModelAbilityMap::can($municipal, ['view', 'update'], user: $user))->toBe([
        'view' => true,
        'update' => false,
    ]);
});

it('returns false for every ability with can when no user is authenticated', function () {
    $municipal = Municipal::factory()->make();

    Gate::define('view', fn () => true);

    expect(ModelAbilityMap::can($municipal, ['view']))->toBe([
        'view' => false,
    ]);
});

it('resolves the user from an explicit guard with can', function () {
    $municipal = Municipal::factory()->make();
    $user = User::factory()->create();

    Gate::define('view', fn (User $authenticatedUser) => $authenticatedUser->is($user));

    $this->actingAs($user, 'administration');

    expect(ModelAbilityMap::can($municipal, ['view'], guard: 'administration'))->toBe([
        'view' => true,
    ]);
});

it('maps abilities for a model class with can', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('create', fn () => true);
    Gate::define('viewAny', fn () => false);

    expect(ModelAbilityMap::can(Municipal::class, ['create', 'viewAny'], user: $user))->toBe([
        'create' => true,
        'viewAny' => false,
    ]);
});

it('returns true from canAny when any ability is granted', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('create', fn () => true);
    Gate::define('viewAny', fn () => false);

    expect(ModelAbilityMap::canAny(Municipal::class, ['create', 'viewAny'], user: $user))->toBeTrue();
});

it('returns false from canAny when no abilities are granted', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('create', fn () => false);
    Gate::define('viewAny', fn () => false);

    expect(ModelAbilityMap::canAny(Municipal::class, ['create', 'viewAny'], user: $user))->toBeFalse();
});

it('returns both can and canAny from make', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('create', fn () => true);
    Gate::define('viewAny', fn () => false);

    expect(ModelAbilityMap::make(Municipal::class, ['create', 'viewAny'], user: $user))->toBe([
        'canAny' => true,
        'can' => [
            'create' => true,
            'viewAny' => false,
        ],
    ]);
});

it('returns canAny false from make when no abilities are granted', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('create', fn () => false);
    Gate::define('viewAny', fn () => false);

    expect(ModelAbilityMap::make(Municipal::class, ['create', 'viewAny'], user: $user))->toBe([
        'canAny' => false,
        'can' => [
            'create' => false,
            'viewAny' => false,
        ],
    ]);
});

it('maps abilities for multiple subject arguments through the bound policy', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    $classroom = Classroom::factory()->makeOne();

    Gate::policy(ClassSchedule::class, ModelAbilityMapMultiSubjectPolicy::class);

    expect(ModelAbilityMap::make([ClassSchedule::class, $classroom], ['update', 'print'], user: $user))->toBe([
        'canAny' => true,
        'can' => [
            'update' => true,
            'print' => false,
        ],
    ]);
});

it('maps abilities for an authorization resource class through its bound policy', function () {
    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::policy(EducationMonitorReport::class, EducationMonitorReportPolicy::class);

    Gate::define('report:education-monitor:view', fn () => true);
    Gate::define('report:education-monitor:print', fn () => false);

    expect(ModelAbilityMap::make(EducationMonitorReport::class, ['view', 'print'], user: $user))->toBe([
        'canAny' => true,
        'can' => [
            'view' => true,
            'print' => false,
        ],
    ]);
});

class ModelAbilityMapMultiSubjectPolicy
{
    public function update(User $user, Classroom $classroom): bool
    {
        return true;
    }

    public function print(User $user, Classroom $classroom): bool
    {
        return false;
    }
}
