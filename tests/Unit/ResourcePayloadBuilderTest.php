<?php

use App\Http\Resources\Administration\MunicipalCollection;
use App\Http\Resources\Administration\UserCollection;
use App\Models\Municipal;
use App\Models\User;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('merges resource data with additional attributes', function () {
    $user = User::factory()->make([
        'id' => 1,
        'uuid' => 'user-uuid',
        'name' => 'Admin User',
        'username' => 'admin',
    ]);

    $resource = new class($user) extends JsonResource
    {
        public function toArray($request): array
        {
            return [
                'id' => $this->resource->id,
                'uuid' => $this->resource->uuid,
                'name' => $this->resource->name,
                'username' => $this->resource->username,
            ];
        }
    };

    expect(ResourcePayloadBuilder::make(
        $resource,
        ['extra' => 'value'],
    ))->toBe([
        'id' => 1,
        'uuid' => 'user-uuid',
        'name' => 'Admin User',
        'username' => 'admin',
        'extra' => 'value',
    ]);
});

it('appends authorization abilities to the resource payload', function () {
    $user = User::factory()->make([
        'id' => 1,
        'uuid' => 'user-uuid',
        'name' => 'Admin User',
        'username' => 'admin',
    ]);

    $resource = new class($user) extends JsonResource
    {
        public function toArray($request): array
        {
            return [
                'id' => $this->resource->id,
                'uuid' => $this->resource->uuid,
                'name' => $this->resource->name,
                'username' => $this->resource->username,
            ];
        }
    };

    Gate::define('view', fn () => true);

    expect(ResourcePayloadBuilder::withAbilities(
        $resource,
        ['view'],
    ))->toMatchArray([
        'id' => 1,
        'uuid' => 'user-uuid',
        'name' => 'Admin User',
        'username' => 'admin',
        'canAny' => false,
        'can' => [
            'view' => false,
        ],
    ]);
});

it('requires the resource to wrap an eloquent model', function () {
    $resource = new class([]) extends JsonResource
    {
        public function toArray($request): array
        {
            return [];
        }
    };

    ResourcePayloadBuilder::withAbilities($resource, ['view']);
})->throws(InvalidArgumentException::class, 'Resource must wrap an Eloquent model.');

it('preserves paginator metadata when resolving a user collection', function () {
    User::factory()->count(2)->create();

    $paginator = User::query()
        ->paginate(1, ['*'], 'page', 1)
        ->withQueryString()
        ->onEachSide(0);

    $payload = ResourcePayloadBuilder::paginate($paginator, UserCollection::make($paginator));

    expect($payload)
        ->toHaveKeys(['data', 'links', 'current_page', 'last_page', 'total'])
        ->and($payload['data'])->toHaveCount(1)
        ->and($payload['data'][0])->toHaveKeys(['id', 'uuid', 'name', 'username', 'scope']);
});

it('preserves paginator metadata when resolving a municipal collection', function () {
    Municipal::factory()->count(2)->create();

    $paginator = Municipal::query()
        ->paginate(1, ['*'], 'page', 1)
        ->withQueryString()
        ->onEachSide(0);

    $payload = ResourcePayloadBuilder::paginate($paginator, MunicipalCollection::make($paginator));

    expect($payload)
        ->toHaveKeys(['data', 'links', 'current_page', 'last_page', 'total'])
        ->and($payload['data'])->toHaveCount(1)
        ->and($payload['data'][0])->toHaveKeys(['id', 'uuid', 'name', 'schools_count']);
});

it('appends row abilities when paginating a resource collection', function () {
    User::factory()->create();

    $paginator = User::query()->paginate()->onEachSide(0);

    Gate::define('view', fn () => true);

    $payload = ResourcePayloadBuilder::paginateWithAbilities($paginator, UserCollection::make($paginator), ['view']);

    expect($payload['data'][0])->toMatchArray([
        'id' => $paginator->items()[0]->id,
        'uuid' => $paginator->items()[0]->uuid,
        'canAny' => false,
        'can' => [
            'view' => false,
        ],
    ]);
});

it('places resolved collection data before paginator metadata', function () {
    User::factory()->create();

    $paginator = User::query()->paginate()->onEachSide(0);

    $payload = ResourcePayloadBuilder::paginate($paginator, UserCollection::make($paginator));

    expect(array_key_first($payload))->toBe('data');
});
