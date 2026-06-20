<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Carbon;

class OtpService
{
    public function __construct(protected SmsService $sms)
    {
    }

    /**
     * Telefon raqamga OTP kod yuborish.
     * Throttle: 1 daqiqada faqat 1 marta.
     *
     * @return array{success: bool, message: string, retry_after?: int, code?: string}
     */
    public function sendCode(string $phone): array
    {
        $phone = $this->sms->normalizePhone($phone);

        $last = OtpCode::where('phone', $phone)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if ($last && $last->created_at->gt(now()->subSeconds(60))) {
            $retryAfter = 60 - now()->diffInSeconds($last->created_at, true);

            return [
                'success' => false,
                'message' => 'Kodni qayta yuborish uchun biroz kuting.',
                'retry_after' => (int) $retryAfter,
            ];
        }

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->sms->send($phone, "Korzinkago tasdiqlash kodi: {$code}. Hech kimga bermang.");

        $result = [
            'success' => true,
            'message' => 'Tasdiqlash kodi yuborildi.',
        ];

        // Test rejimida kodni javobda qaytaramiz (faqat local)
        if (config('services.eskiz.fake')) {
            $result['code'] = $code;
        }

        return $result;
    }

    /**
     * OTP kodni tekshirish.
     *
     * @return array{success: bool, message: string}
     */
    public function verifyCode(string $phone, string $code): array
    {
        $phone = $this->sms->normalizePhone($phone);

        $otp = OtpCode::where('phone', $phone)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp) {
            return ['success' => false, 'message' => 'Kod topilmadi. Qayta yuboring.'];
        }

        if ($otp->isExpired()) {
            return ['success' => false, 'message' => 'Kod muddati tugagan.'];
        }

        if ($otp->attempts >= 5) {
            return ['success' => false, 'message' => 'Urinishlar soni tugadi. Yangi kod so\'rang.'];
        }

        $otp->increment('attempts');

        if ($otp->code !== $code) {
            return ['success' => false, 'message' => 'Kod noto\'g\'ri.'];
        }

        $otp->update(['verified_at' => Carbon::now()]);

        return ['success' => true, 'message' => 'Tasdiqlandi.'];
    }
}
