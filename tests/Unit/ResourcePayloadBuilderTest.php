<?php

use App\Http\Resources\Administration\MunicipalResource;
use App\Models\Municipal;
use App\Support\Http\ResourcePayloadBuilder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class);

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
