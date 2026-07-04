<?php

use App\Http\Resources\Administration\MunicipalResource;
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
    $municipal = Municipal::factory()->make([
        'id' => 1,
        'uuid' => 'municipal-uuid',
        'name' => 'Tripoli',
    ]);

    expect(ResourcePayloadBuilder::make(
        MunicipalResource::make($municipal),
        ['extra' => 'value'],
    ))->toBe([
        'id' => 1,
        'uuid' => 'municipal-uuid',
        'name' => 'Tripoli',
        'schools_count' => null,
        'extra' => 'value',
    ]);
});

it('appends authorization abilities to the resource payload', function () {
    $municipal = Municipal::factory()->make([
        'id' => 1,
        'uuid' => 'municipal-uuid',
        'name' => 'Tripoli',
    ]);

    Gate::define('view', fn () => true);

    expect(ResourcePayloadBuilder::withAbilities(
        MunicipalResource::make($municipal),
        ['view'],
    ))->toMatchArray([
        'id' => 1,
        'uuid' => 'municipal-uuid',
        'name' => 'Tripoli',
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

it('preserves paginator metadata when resolving a resource collection', function () {
    User::factory()->count(2)->create();

    $paginator = User::query()
        ->paginate(1, ['*'], 'page', 1)
        ->withQueryString()
        ->onEachSide(0);

    $payload = ResourcePayloadBuilder::paginate($paginator, UserCollection::make($paginator));

    expect($payload)
        ->toHaveKeys(['data', 'links', 'current_page', 'last_page', 'total'])
        ->and($payload['data'])->toHaveCount(1)
        ->and($payload['data'][0])->toHaveKeys(['id', 'name', 'email', 'scope']);
});

it('appends row abilities when paginating a resource collection', function () {
    User::factory()->create();

    $paginator = User::query()->paginate()->onEachSide(0);

    Gate::define('view', fn () => true);

    $payload = ResourcePayloadBuilder::paginateWithAbilities($paginator, UserCollection::make($paginator), ['view']);

    expect($payload['data'][0])->toMatchArray([
        'id' => $paginator->items()[0]->id,
        'canAny' => false,
        'can' => [
            'view' => false,
        ],
    ]);
});
