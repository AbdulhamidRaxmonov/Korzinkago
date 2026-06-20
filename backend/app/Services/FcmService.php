<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (HTTP v1) orqali push-bildirishnoma yuborish.
 * Service account JSON kalitidan OAuth2 token olinadi.
 */
class FcmService
{
    /**
     * Bitta foydalanuvchining barcha qurilmalariga yuborish.
     */
    public function sendToUser(User|int $user, string $title, string $body, array $data = []): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->all();

        foreach ($tokens as $token) {
            $this->send($token, $title, $body, $data);
        }
    }

    /**
     * Bitta token bo'yicha yuborish.
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (config('services.fcm.fake')) {
            Log::info("[FAKE FCM] {$title} -> {$token}: {$body}", $data);

            return true;
        }

        $accessToken = $this->accessToken();
        $projectId = config('services.fcm.project_id');

        if (! $accessToken || ! $projectId) {
            Log::warning('FCM sozlanmagan (project_id yoki credentials yo\'q)');

            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => array_map('strval', $data),
                        'android' => ['priority' => 'high'],
                    ],
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('FCM xato: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Service account JSON dan OAuth2 access token olish (1 soat cache).
     */
    protected function accessToken(): ?string
    {
        return Cache::remember('fcm_access_token', now()->addMinutes(55), function () {
            $path = config('services.fcm.credentials');

            if (! $path || ! file_exists($path)) {
                return null;
            }

            $sa = json_decode(file_get_contents($path), true);
            $now = time();

            $jwt = $this->signJwt([
                'iss' => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], $sa['private_key']);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json('access_token');
        });
    }

    /**
     * RS256 imzoli JWT yaratish.
     */
    protected function signJwt(array $claims, string $privateKey): string
    {
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64Url(json_encode($claims));
        $unsigned = "{$header}.{$payload}";

        openssl_sign($unsigned, $signature, $privateKey, 'sha256WithRSAEncryption');

        return "{$unsigned}.".$this->base64Url($signature);
    }

    protected function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
