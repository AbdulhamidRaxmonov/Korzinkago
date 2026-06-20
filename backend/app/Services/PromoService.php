<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\User;

class PromoService
{
    /**
     * Promokodni tekshirish.
     *
     * @return array{valid: bool, message: string, discount?: float, promo?: PromoCode}
     */
    public function validate(string $code, User $user, float $itemsTotal): array
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (! $promo || ! $promo->is_active) {
            return ['valid' => false, 'message' => 'Promokod topilmadi yoki faol emas.'];
        }

        if ($promo->starts_at && $promo->starts_at->isFuture()) {
            return ['valid' => false, 'message' => 'Promokod hali faollashmagan.'];
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'Promokod muddati tugagan.'];
        }

        if ($itemsTotal < $promo->min_order) {
            return [
                'valid' => false,
                'message' => 'Minimal buyurtma summasi: '.number_format($promo->min_order).' so\'m.',
            ];
        }

        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            return ['valid' => false, 'message' => 'Promokod limiti tugagan.'];
        }

        $userUsageCount = PromoCodeUsage::where('promo_code_id', $promo->id)
            ->where('user_id', $user->id)
            ->count();

        if ($userUsageCount >= $promo->per_user_limit) {
            return ['valid' => false, 'message' => 'Siz bu promokoddan foydalangansiz.'];
        }

        if ($promo->first_order_only && $user->orders()->exists()) {
            return ['valid' => false, 'message' => 'Promokod faqat birinchi buyurtma uchun.'];
        }

        return [
            'valid' => true,
            'message' => 'Promokod qo\'llandi!',
            'discount' => $promo->discountFor($itemsTotal),
            'promo' => $promo,
        ];
    }

    /**
     * Promokod ishlatilganini qayd etish (buyurtma yaratilganda).
     */
    public function recordUsage(PromoCode $promo, User $user, Order $order, float $discount): void
    {
        PromoCodeUsage::create([
            'promo_code_id' => $promo->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount' => $discount,
        ]);

        $promo->increment('used_count');
    }
}
