<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ClickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    public function __construct(protected ClickService $click)
    {
    }

    // Click xato kodlari
    private const SUCCESS = 0;
    private const ERROR_SIGN = -1;
    private const ERROR_BAD_REQUEST = -8;
    private const ERROR_USER_NOT_FOUND = -5;
    private const ERROR_TRANSACTION_NOT_FOUND = -6;
    private const ERROR_ALREADY_PAID = -4;
    private const ERROR_CANCELLED = -9;
    private const ERROR_BAD_AMOUNT = -2;

    /**
     * Foydalanuvchi uchun Click to'lov linkini qaytarish.
     */
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
        ]);

        $order = $request->user()->orders()->findOrFail($data['order_id']);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Buyurtma allaqachon to\'langan.'], 422);
        }

        return response()->json([
            'checkout_url' => $this->click->checkoutUrl($order, $request->input('return_url')),
            'amount' => $order->total,
        ]);
    }

    /**
     * Click "Prepare" webhook.
     */
    public function prepare(Request $request): JsonResponse
    {
        $r = $request->all();

        if (! $this->click->checkPrepareSign($r)) {
            return $this->response($r, self::ERROR_SIGN, 'Imzo xato');
        }

        $order = Order::find($r['merchant_trans_id'] ?? null);
        if (! $order) {
            return $this->response($r, self::ERROR_USER_NOT_FOUND, 'Buyurtma topilmadi');
        }

        if ((int) round($order->total) !== (int) round((float) $r['amount'])) {
            return $this->response($r, self::ERROR_BAD_AMOUNT, 'Summa noto\'g\'ri');
        }

        if ($order->payment_status === 'paid') {
            return $this->response($r, self::ERROR_ALREADY_PAID, 'Allaqachon to\'langan');
        }

        $payment = Payment::updateOrCreate(
            ['transaction_id' => $r['click_trans_id'], 'provider' => 'click'],
            ['order_id' => $order->id, 'amount' => $r['amount'], 'state' => 'pending'],
        );

        return $this->response($r, self::SUCCESS, 'Muvaffaqiyatli', [
            'merchant_prepare_id' => $payment->id,
        ]);
    }

    /**
     * Click "Complete" webhook.
     */
    public function complete(Request $request): JsonResponse
    {
        $r = $request->all();

        if (! $this->click->checkCompleteSign($r)) {
            return $this->response($r, self::ERROR_SIGN, 'Imzo xato');
        }

        $payment = Payment::where('transaction_id', $r['click_trans_id'])
            ->where('provider', 'click')
            ->first();

        if (! $payment) {
            return $this->response($r, self::ERROR_TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        $order = $payment->order;

        // Click foydalanuvchi tomonidan bekor qilingan bo'lsa (error < 0)
        if ((int) ($r['error'] ?? 0) < 0) {
            $payment->update(['state' => 'cancelled']);
            $order?->update(['payment_status' => 'failed']);

            return $this->response($r, self::ERROR_CANCELLED, 'Bekor qilindi');
        }

        if ($order && $order->payment_status === 'paid') {
            return $this->response($r, self::ERROR_ALREADY_PAID, 'Allaqachon to\'langan');
        }

        $payment->update(['state' => 'paid']);
        $order?->update(['payment_status' => 'paid']);
        $order?->logStatus($order->status, null, 'Click orqali to\'landi');

        return $this->response($r, self::SUCCESS, 'Muvaffaqiyatli', [
            'merchant_prepare_id' => $payment->id,
            'merchant_confirm_id' => $payment->id,
        ]);
    }

    /**
     * Click javob formati.
     */
    private function response(array $r, int $error, string $note, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'click_trans_id' => $r['click_trans_id'] ?? null,
            'merchant_trans_id' => $r['merchant_trans_id'] ?? null,
            'error' => $error,
            'error_note' => $note,
        ], $extra));
    }
}
