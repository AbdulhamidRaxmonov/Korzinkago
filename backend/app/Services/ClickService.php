<?php

namespace App\Services;

use App\Models\Order;

/**
 * Click (Merchant API) yordamchisi.
 * Hujjat: https://docs.click.uz/
 */
class ClickService
{
    /**
     * Click to'lov sahifasi (Click Pass / Checkout) URL yaratish.
     */
    public function checkoutUrl(Order $order, ?string $returnUrl = null): string
    {
        $cfg = config('services.click');

        $params = [
            'service_id' => $cfg['service_id'],
            'merchant_id' => $cfg['merchant_id'],
            'amount' => $order->total,
            'transaction_param' => $order->id,
        ];

        if ($returnUrl) {
            $params['return_url'] = $returnUrl;
        }

        return rtrim($cfg['checkout_url'], '/').'/services/pay?'.http_build_query($params);
    }

    /**
     * Click "prepare" bosqichidagi imzoni tekshirish.
     * sign = md5(click_trans_id + service_id + SECRET_KEY + merchant_trans_id + amount + action + sign_time)
     */
    public function checkPrepareSign(array $r): bool
    {
        $secret = config('services.click.secret_key');
        $hash = md5(
            $r['click_trans_id'].
            $r['service_id'].
            $secret.
            $r['merchant_trans_id'].
            $r['amount'].
            $r['action'].
            $r['sign_time']
        );

        return hash_equals($hash, $r['sign_string'] ?? '');
    }

    /**
     * Click "complete" bosqichidagi imzoni tekshirish.
     * complete'da merchant_prepare_id ham qo'shiladi.
     */
    public function checkCompleteSign(array $r): bool
    {
        $secret = config('services.click.secret_key');
        $hash = md5(
            $r['click_trans_id'].
            $r['service_id'].
            $secret.
            $r['merchant_trans_id'].
            ($r['merchant_prepare_id'] ?? '').
            $r['amount'].
            $r['action'].
            $r['sign_time']
        );

        return hash_equals($hash, $r['sign_string'] ?? '');
    }
}
