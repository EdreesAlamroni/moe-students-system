<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LocationSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $response = Http::withHeaders([
            'User-Agent' => Str::of(config('app.name'))
                ->append(' (', config('app.url'), ')')
                ->toString(),
            'Accept-Language' => 'ar,en',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'json',
            'q' => $validated['query'],
            'limit' => 5,
            'countrycodes' => 'ly',
            'addressdetails' => 1,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'تعذّر البحث عن الموقع.',
            ], 502);
        }

        $results = collect($response->json())
            ->map(fn (array $item): array => [
                'id' => intval($item['place_id']),
                'label' => $this->locationLabel($item),
                'latitude' => floatval($item['lat']),
                'longitude' => floatval($item['lon']),
            ])
            ->filter(fn (array $item): bool => $item['label'] !== '')
            ->values()
            ->all();

        return response()->json([
            'results' => $results,
        ]);
    }

    private function locationLabel(array $item): string
    {
        $parts = $this->addressParts($item['address'] ?? []);

        if ($parts === []) {
            $parts = $this->displayNameParts((string) ($item['display_name'] ?? ''));
        }

        if ($parts === []) {
            return '';
        }

        $arabicParts = array_values(array_filter(
            $parts,
            fn (string $part): bool => $this->containsArabic($part),
        ));

        if ($arabicParts !== []) {
            return implode('، ', $arabicParts);
        }

        $englishParts = array_values(array_filter(
            $parts,
            fn (string $part): bool => $this->containsEnglish($part),
        ));

        return implode('، ', $englishParts);
    }

    private function addressParts(mixed $address): array
    {
        if (! is_array($address)) {
            return [];
        }

        return $this->normalizeParts([
            $address['amenity'] ?? $address['building'] ?? null,
            $address['road'] ?? null,
            $address['suburb'] ?? $address['neighbourhood'] ?? null,
            $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
            $address['state'] ?? null,
            $address['country'] ?? null,
        ]);
    }

    private function displayNameParts(string $displayName): array
    {
        return $this->normalizeParts(preg_split('/\s*[,،]\s*/u', $displayName) ?: []);
    }

    private function normalizeParts(array $parts): array
    {
        $normalizedParts = [];
        $seen = [];

        foreach ($parts as $part) {
            if (! is_string($part)) {
                continue;
            }

            $normalizedPart = preg_replace('/\s+/u', ' ', trim($part)) ?? '';
            $normalizedPart = trim(preg_replace('/[^0-9A-Za-z\x{0600}-\x{06FF}\s\-\/().]+/u', '', $normalizedPart) ?? '');

            if ($normalizedPart === '') {
                continue;
            }

            if (! $this->containsArabic($normalizedPart) && ! $this->containsEnglish($normalizedPart)) {
                continue;
            }

            $key = mb_strtolower($normalizedPart);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalizedParts[] = $normalizedPart;
        }

        return $normalizedParts;
    }

    private function containsArabic(string $value): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $value) === 1;
    }

    private function containsEnglish(string $value): bool
    {
        return preg_match('/[A-Za-z]/', $value) === 1;
    }
}
