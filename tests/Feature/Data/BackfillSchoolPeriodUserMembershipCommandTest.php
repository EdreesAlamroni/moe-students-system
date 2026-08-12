<?php

use App\Enums\UserScope;
use App\Models\SchoolPeriod;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('backfill command inserts memberships from existing school users', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $user->schoolPeriods()->detach();

    Artisan::call('data:backfill-school-period-user-membership');

    expect($user->schoolPeriods()->pluck('school_periods.id')->all())->toBe([$schoolPeriod->id]);
});

test('backfill command is idempotent', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();

    User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    expect(Artisan::call('data:backfill-school-period-user-membership'))->toBe(Command::SUCCESS)
        ->and(Artisan::call('data:backfill-school-period-user-membership'))->toBe(Command::SUCCESS)
        ->and(DB::table('school_period_user')->count())->toBe(1);
});
