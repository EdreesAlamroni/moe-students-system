<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests cannot search locations', function () {
    $this->getJson(route('locations.search', ['query' => 'Benghazi']))
        ->assertUnauthorized();
});

test('authenticated users can search locations', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            [
                'place_id' => 123456,
                'display_name' => 'بنغازي، ليبيا',
                'lat' => '32.1167',
                'lon' => '20.0667',
                'address' => [
                    'city' => 'بنغازي',
                    'country' => 'ليبيا',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->getJson(route('locations.search', ['query' => 'Benghazi']))
        ->assertSuccessful()
        ->assertJson([
            'results' => [
                [
                    'id' => 123456,
                    'label' => 'بنغازي، ليبيا',
                    'latitude' => 32.1167,
                    'longitude' => 20.0667,
                ],
            ],
        ]);
});

test('location search labels include only arabic and english text', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            [
                'place_id' => 998877,
                'display_name' => 'Бенгази، ليبيا، Benghazi',
                'lat' => '32.1167',
                'lon' => '20.0667',
                'address' => [
                    'city' => 'Бенгази',
                    'country' => 'ليبيا',
                ],
            ],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->getJson(route('locations.search', ['query' => 'Benghazi']))
        ->assertSuccessful()
        ->assertJsonPath('results.0.label', 'ليبيا');
});

test('location search validates the query', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->getJson(route('locations.search', ['query' => 'a']));

    expect($response->status())->toBe(422)
        ->and($response->json('errors.query'))->not->toBeEmpty();
});

test('location search returns an error when the geocoder fails', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([], 500),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->getJson(route('locations.search', ['query' => 'Benghazi']))
        ->assertStatus(502)
        ->assertJson([
            'message' => 'تعذّر البحث عن الموقع.',
        ]);
});
