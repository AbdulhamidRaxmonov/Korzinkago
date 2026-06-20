<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function __construct(protected PromoService $promo)
    {
    }

    /**
     * Savatga promokodni qo'llashni tekshirish (checkout oldidan).
     */
    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $itemsTotal = $user->cartItems()->with('product')->get()
            ->sum(fn ($i) => $i->product ? $i->product->price * $i->quantity : 0);

        $result = $this->promo->validate($data['code'], $user, $itemsTotal);

        if (! $result['valid']) {
            return response()->json(['valid' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => $result['message'],
            'code' => strtoupper(trim($data['code'])),
            'discount' => $result['discount'],
            'items_total' => $itemsTotal,
            'total_after_discount' => round($itemsTotal - $result['discount'], 2),
        ]);
    }
}
