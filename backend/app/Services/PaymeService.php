<?php

namespace App\Services;

use App\Models\Order;

/**
 * Payme (Paycom) Merchant API yordamchisi.
 * Hujjat: https://developer.help.paycom.uz/
 */
class PaymeService
{
    /**
     * Payme checkout (to'lov sahifasi) uchun URL yaratish.
     * Summa tiyinda bo'lishi kerak (so'm * 100).
     */
    public function checkoutUrl(Order $order, ?string $returnUrl = null): string
    {
        $merchantId = config('services.payme.merchant_id');
        $accountField = config('services.payme.account_field', 'order_id');

        $params = [
            "m={$merchantId}",
            "ac.{$accountField}={$order->id}",
            'a='.(int) round($order->total * 100), // tiyin
        ];

        if ($returnUrl) {
            $params[] = "c={$returnUrl}";
        }

        $encoded = base64_encode(implode(';', $params));

        return rtrim(config('services.payme.checkout_url'), '/').'/'.$encoded;
    }

    /**
     * Payme so'rovidagi Basic auth ni tekshirish.
     */
    public function authenticate(?string $header): bool
    {
        if (! $header || ! str_starts_with($header, 'Basic ')) {
            return false;
        }

        $decoded = base64_decode(substr($header, 6));
        [$login, $key] = array_pad(explode(':', $decoded, 2), 2, '');

        $validKey = config('services.payme.key');
        $testKey = config('services.payme.test_key');

        return $login === 'Paycom' && ($key === $validKey || ($testKey && $key === $testKey));
    }
}
