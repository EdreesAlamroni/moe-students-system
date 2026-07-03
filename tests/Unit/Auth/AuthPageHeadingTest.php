<?php

use App\Enums\AuthPage;
use App\Support\Auth\DashboardAuth;
use Tests\TestCase;

uses(TestCase::class);

test('auth page heading interpolates the dashboard label', function (DashboardAuth $dashboard, AuthPage $page, string $expectedTitleFragment) {
    $heading = $dashboard->authPageHeading($page);

    expect($heading)
        ->toHaveKeys(['title', 'description'])
        ->and($heading['title'])->toContain($expectedTitleFragment)
        ->and($heading['description'])->not->toBe('');
})->with([
    'administration login' => [DashboardAuth::administration(), AuthPage::LOGIN, 'الإدارة'],
    'warehouse forgot password' => [DashboardAuth::warehouse(), AuthPage::FORGOT_PASSWORD, 'استعادة'],
    'school reset password' => [DashboardAuth::school(), AuthPage::RESET_PASSWORD, 'إعادة'],
    'education monitor confirm password' => [DashboardAuth::educationMonitor(), AuthPage::CONFIRM_PASSWORD, 'تأكيد'],
    'education services office change password' => [DashboardAuth::educationServicesOffice(), AuthPage::CHANGE_PASSWORD, 'تغيير'],
]);

test('auth routes omit password reset endpoints when disabled', function () {
    expect(DashboardAuth::school()->authRoutes())
        ->not->toHaveKeys(['forgotPassword', 'forgotPasswordStore', 'resetPasswordStore']);
});
