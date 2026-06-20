<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eskiz.uz SMS provayderi bilan ishlash.
 * Hujjat: https://documenter.getpostman.com/view/663428/RzfmES4z
 */
class SmsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.eskiz.base_url'), '/');
    }

    /**
     * SMS yuborish. Test rejimida (SMS_FAKE=true) faqat logga yoziladi.
     */
    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);

        if (config('services.eskiz.fake')) {
            Log::info("[FAKE SMS] {$phone}: {$message}");

            return true;
        }

        try {
            $response = Http::withToken($this->token())
                ->asForm()
                ->post("{$this->baseUrl}/message/sms/send", [
                    'mobile_phone' => $phone,
                    'message' => $message,
                    'from' => config('services.eskiz.sender'),
                ]);

            if ($response->successful()) {
                return true;
            }

            // Token muddati tugagan bo'lishi mumkin — yangilab qayta urinish
            if ($response->status() === 401) {
                Cache::forget('eskiz_token');

                $retry = Http::withToken($this->token())
                    ->asForm()
                    ->post("{$this->baseUrl}/message/sms/send", [
                        'mobile_phone' => $phone,
                        'message' => $message,
                        'from' => config('services.eskiz.sender'),
                    ]);

                return $retry->successful();
            }

            Log::warning('Eskiz SMS xato', ['body' => $response->body()]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Eskiz SMS exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Eskiz tokenini olish (30 kun cache'lanadi).
     */
    protected function token(): ?string
    {
        return Cache::remember('eskiz_token', now()->addDays(25), function () {
            $response = Http::asForm()->post("{$this->baseUrl}/auth/login", [
                'email' => config('services.eskiz.email'),
                'password' => config('services.eskiz.password'),
            ]);

            return $response->json('data.token');
        });
    }

    /**
     * Telefon raqamni 998XXXXXXXXX formatiga keltirish.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '998')) {
            return $phone;
        }

        if (strlen($phone) === 9) {
            return '998'.$phone;
        }

        return $phone;
    }
}
