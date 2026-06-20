<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * FCM qurilma tokenini saqlash (push-bildirishnoma uchun).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'in:android,ios'],
        ]);

        DeviceToken::updateOrCreate(
            ['user_id' => $request->user()->id, 'token' => $data['token']],
            ['platform' => $data['platform'] ?? null],
        );

        return response()->json(['success' => true]);
    }
}
