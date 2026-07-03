<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\RequestState\UserRequestState;
use App\ModelStates\User\State\Activated;
use App\ModelStates\User\State\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'name' => fake()->name(),
            'username' => $username,
            'email' => "{$username}@example.com",
            'scope' => UserScope::ADMINISTRATION,
            'role' => UserRole::MANAGER,
            'state' => Activated::class,
            'request_state' => Approved::class,
            'must_change_password' => false,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function withScope(UserScope $scope): static
    {
        return $this->state(['scope' => $scope]);
    }

    public function withRole(UserRole $role): static
    {
        return $this->state(['role' => $role]);
    }

    /**
     * @param  class-string<UserState>  $state
     */
    public function withState(string $state): static
    {
        return $this->state(['state' => $state]);
    }

    /**
     * @param  class-string<UserRequestState>  $requestState
     */
    public function withRequestState(string $requestState): static
    {
        return $this->state(['request_state' => $requestState]);
    }

    public function withMustChangePassword(bool $mustChangePassword = true): static
    {
        return $this->state(['must_change_password' => $mustChangePassword]);
    }
}
