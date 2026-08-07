<?php

use App\Support\UniqueUsernameConstraint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

test('unique username constraint violations are converted to validation errors', function () {
    $exception = new UniqueConstraintViolationException(
        'pgsql',
        'insert into "users"',
        [],
        new PDOException('Unique violation: 7 ERROR:  duplicate key value violates unique constraint "users_username_unique"'),
    );

    expect(UniqueUsernameConstraint::isViolation($exception))->toBeTrue();

    try {
        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render(Request::create('/administration/users', 'POST'), $exception);

        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $validationException) {
        expect($validationException->errors())->toHaveKey('username')
            ->and($validationException->errors()['username'][0])->toBe(UniqueUsernameConstraint::validationMessage());
    }
});
