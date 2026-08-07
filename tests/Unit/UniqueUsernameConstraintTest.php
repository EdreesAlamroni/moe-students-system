<?php

use App\Support\UniqueUsernameConstraint;
use Illuminate\Database\UniqueConstraintViolationException;

test('detects postgres username unique constraint violations', function () {
    $exception = new UniqueConstraintViolationException(
        'pgsql',
        'insert into "users"',
        [],
        new PDOException('Unique violation: 7 ERROR:  duplicate key value violates unique constraint "users_username_unique"'),
    );

    expect(UniqueUsernameConstraint::isViolation($exception))->toBeTrue();
});

test('detects sqlite username unique constraint violations', function () {
    $exception = new UniqueConstraintViolationException(
        'sqlite',
        'insert into "users"',
        [],
        new PDOException('UNIQUE constraint failed: users.username'),
    );

    expect(UniqueUsernameConstraint::isViolation($exception))->toBeTrue();
});

test('ignores unrelated unique constraint violations', function () {
    $exception = new UniqueConstraintViolationException(
        'pgsql',
        'insert into "users"',
        [],
        new PDOException('Unique violation: 7 ERROR:  duplicate key value violates unique constraint "users_email_unique"'),
    );

    expect(UniqueUsernameConstraint::isViolation($exception))->toBeFalse();
});
