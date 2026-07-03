<?php

use App\Http\Middleware\EnforceAcademicYearReadOnly;
use App\Models\AcademicYear;
use App\Models\User;
use App\Support\AcademicYearReadOnlyExemptions;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();

    RouteFacade::middleware(['web', 'auth:administration', 'bind.dashboard:administration', EnforceAcademicYearReadOnly::class])
        ->match([
            'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE',
        ], '/administration/_test/mutation', function (): Response {
            return response()->noContent();
        })
        ->name('administration.test.mutation');

    RouteFacade::getRoutes()->refreshNameLookups();
});

test('read only middleware allows safe http methods', function (string $method) {
    $user = User::factory()->create();
    $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($user, 'administration')
        ->withSession([sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id])
        ->call($method, '/administration/_test/mutation')
        ->assertSuccessful();
})->with([
    'GET' => ['GET'],
    'HEAD' => ['HEAD'],
    'OPTIONS' => ['OPTIONS'],
]);

test('read only middleware blocks mutating requests for inactive academic years', function (string $method) {
    $user = User::factory()->create();
    $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($user, 'administration')
        ->withSession([sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id])
        ->call($method, '/administration/_test/mutation')
        ->assertForbidden()
        ->assertSee(__('Modifications are not allowed for previous academic years.'));
})->with([
    'POST' => ['POST'],
    'PUT' => ['PUT'],
    'PATCH' => ['PATCH'],
    'DELETE' => ['DELETE'],
]);

test('read only middleware allows mutating requests for active academic years', function (string $method) {
    $user = User::factory()->create();
    $activeYear = AcademicYear::factory()->active()->create();

    $this->actingAs($user, 'administration')
        ->withSession([sprintf('selected_academic_year_id.%d', $user->id) => $activeYear->id])
        ->call($method, '/administration/_test/mutation')
        ->assertSuccessful();
})->with([
    'POST' => ['POST'],
    'PUT' => ['PUT'],
    'PATCH' => ['PATCH'],
    'DELETE' => ['DELETE'],
]);

test('read only middleware allows mutating requests when no academic year is available', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->post('/administration/_test/mutation')
        ->assertSuccessful();
});

test('read only middleware allows mutating requests on exempt routes when academic year is inactive', function (string $routeName) {
    $user = User::factory()->create();
    $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($user, 'administration');
    app()->instance(DashboardAuth::class, DashboardAuth::administration());

    session([sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id]);

    $middleware = new EnforceAcademicYearReadOnly;
    $request = Request::create('/test', 'POST');
    $request->setRouteResolver(function () use ($routeName): Route {
        $route = new Route('POST', '/test', []);
        $route->name($routeName);

        return $route;
    });

    $response = $middleware->handle($request, function (): Response {
        return response('allowed', 200);
    });

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('allowed');
})->with(function (): array {
    $routes = [];

    foreach (AcademicYearReadOnlyExemptions::routeNames() as $routeName) {
        if ($routeName === 'academic-year.select' || str_starts_with($routeName, 'administration.')) {
            $routes[] = $routeName;
        }
    }

    return $routes;
});
