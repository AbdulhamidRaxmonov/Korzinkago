<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Yetkazib berish masofasi va narxini hisoblash.
 * Google Distance Matrix API ishlatiladi, mavjud bo'lmasa Haversine.
 */
class DeliveryService
{
    /**
     * Ikki nuqta orasidagi masofani (km) qaytaradi.
     */
    public function distanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $key = config('services.google_maps.key');

        if ($key) {
            try {
                $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => "{$fromLat},{$fromLng}",
                    'destinations' => "{$toLat},{$toLng}",
                    'mode' => 'driving',
                    'key' => $key,
                ]);

                $meters = $response->json('rows.0.elements.0.distance.value');

                if ($meters !== null) {
                    return round($meters / 1000, 2);
                }
            } catch (\Throwable $e) {
                Log::warning('Distance Matrix xato: '.$e->getMessage());
            }
        }

        return $this->haversine($fromLat, $fromLng, $toLat, $toLng);
    }

    /**
     * Yetkazib berish narxini hisoblash.
     */
    public function calculateFee(float $distanceKm, float $itemsTotal): float
    {
        $cfg = config('services.delivery');

        if ($itemsTotal >= $cfg['free_from']) {
            return 0;
        }

        $fee = $cfg['base_fee'] + ($distanceKm * $cfg['per_km']);

        return round($fee / 1000) * 1000; // eng yaqin 1000 so'mga
    }

    /**
     * Haversine formula bilan masofa (km).
     */
    public function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earth * $c, 2);
    }

    /**
     * Manzilni koordinataga aylantirish (geocoding).
     *
     * @return array{lat: float, lng: float}|null
     */
    public function geocode(string $address): ?array
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $key,
            ]);

            $location = $response->json('results.0.geometry.location');

            if ($location) {
                return ['lat' => (float) $location['lat'], 'lng' => (float) $location['lng']];
            }
        } catch (\Throwable $e) {
            Log::warning('Geocode xato: '.$e->getMessage());
        }

        return null;
    }
}
