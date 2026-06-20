<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    public function __construct(protected DeliveryService $delivery)
    {
    }

    /**
     * Manzil matnidan koordinata (geocoding).
     */
    public function geocode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'min:3'],
        ]);

        $result = $this->delivery->geocode($data['address']);

        if (! $result) {
            return response()->json(['message' => 'Manzil topilmadi.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Koordinatadan manzil (reverse geocoding).
     */
    public function reverse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $key = config('services.google_maps.key');

        if (! $key) {
            return response()->json(['address' => "{$data['lat']}, {$data['lng']}"]);
        }

        $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$data['lat']},{$data['lng']}",
            'language' => 'uz',
            'key' => $key,
        ]);

        $address = $response->json('results.0.formatted_address');

        return response()->json([
            'address' => $address ?? "{$data['lat']}, {$data['lng']}",
        ]);
    }
}
