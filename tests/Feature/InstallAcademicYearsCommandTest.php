<?php

use App\Models\AcademicYear;
use App\Models\User;
use App\Policies\Administration\AcademicYearPolicy;
use App\Support\AcademicYearCalendar;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

test('activate marks the chosen academic year as active', function () {
    $year = AcademicYear::factory()->forStartYear(2026)->create(['is_active' => false]);

    AcademicYear::activate($year);

    expect(AcademicYear::query()->active()->first()?->name)->toBe('2027/2026');
});

test('install command creates historical years and activates the chosen year', function () {
    $this->artisan('setup:install-academic-years')
        ->expectsQuestion('Start year for the active academic year (e.g. 2026 creates 2027/2026)', '2026')
        ->expectsOutputToContain('Active year: 2027/2026')
        ->assertExitCode(Command::SUCCESS);

    expect(AcademicYear::query()->count())->toBe(31)
        ->and(AcademicYear::query()->active()->first()?->name)->toBe('2027/2026');
});

test('install command skips when academic years already exist', function () {
    AcademicYear::factory()->forStartYear(2025)->active()->create();

    $this->artisan('setup:install-academic-years')
        ->assertExitCode(Command::SUCCESS);

    expect(AcademicYear::query()->count())->toBe(1);
});

test('install command fails without interaction when no academic years exist', function () {
    $this->artisan('setup:install-academic-years', ['--no-interaction' => true])
        ->assertExitCode(Command::FAILURE);

    expect(AcademicYear::query()->count())->toBe(0);
});

test('create and activate next year deactivates the previous active year', function () {
    $currentYear = AcademicYear::factory()->forStartYear(2025)->active()->create();

    $nextAttributes = AcademicYearCalendar::attributesForStartYear(2026, true);
    $nextYear = AcademicYear::createNewYear($nextAttributes);

    expect($nextYear->name)->toBe('2027/2026')
        ->and($nextYear->is_active)->toBeTrue()
        ->and($currentYear->fresh()->is_active)->toBeFalse()
        ->and(AcademicYear::query()->active()->first()?->id)->toBe($nextYear->id);
});

test('start year is derived from the academic year name', function () {
    $year = AcademicYear::factory()->create([
        'name' => '2027/2026',
        'start_date' => '2026-08-15',
        'end_date' => '2027-06-30',
        'is_active' => false,
    ]);

    expect($year->startYear())->toBe(2026);
});

test('next start year prefers the year after the active academic year', function () {
    AcademicYear::factory()->forStartYear(2025)->active()->create();

    expect(AcademicYear::nextStartYear())->toBe(2026);
});

test('next start year falls back to the latest record when the next active year already exists', function () {
    AcademicYear::factory()->forStartYear(2025)->active()->create();
    AcademicYear::factory()->forStartYear(2026)->create(['is_active' => false]);

    expect(AcademicYear::nextStartYear())->toBe(2027);
});

test('next start year falls back to the calendar when the database is empty', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-08'));

    expect(AcademicYear::nextStartYear())->toBe(2025);
});

test('defaults for create form use the next database backed academic year', function () {
    AcademicYear::factory()->forStartYear(2025)->active()->create();

    expect(AcademicYear::defaultsForCreateForm())->toBe([
        'name' => '2027/2026',
        'min_start_date' => '2026-09-01',
        'max_end_date' => '2027-06-30',
    ]);
});

test('current academic year resolves from the active database record', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-08'));

    $activeYear = AcademicYear::factory()->forStartYear(2026)->active()->create();
    AcademicYear::factory()->forStartYear(2025)->create(['is_active' => false]);

    AcademicYear::clearCachedCurrent();

    expect(AcademicYear::current()?->id)->toBe($activeYear->id);
});

test('academic year create policy allows create when there is no active year', function () {
    Permission::findOrCreate('academic-year:create', 'administration');

    $user = User::factory()->create();
    $user->givePermissionTo('academic-year:create');

    AcademicYear::factory()->forStartYear(2025)->create(['is_active' => false]);

    expect((new AcademicYearPolicy)->create($user))->toBeTrue();
});

test('academic year create policy blocks create when an active year exists', function () {
    Permission::findOrCreate('academic-year:create', 'administration');

    $user = User::factory()->create();
    $user->givePermissionTo('academic-year:create');

    AcademicYear::factory()->forStartYear(2025)->active()->create();

    expect((new AcademicYearPolicy)->create($user))->toBeFalse();
});
