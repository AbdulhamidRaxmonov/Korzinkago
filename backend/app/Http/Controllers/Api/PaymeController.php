<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PaymeException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymeController extends Controller
{
    public function __construct(protected PaymeService $payme)
    {
    }

    /**
     * Foydalanuvchi uchun Payme to'lov sahifasi linkini qaytarish.
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
            'checkout_url' => $this->payme->checkoutUrl($order, $request->input('return_url')),
            'amount' => (int) round($order->total * 100),
        ]);
    }

    /**
     * Payme Merchant API webhook (JSON-RPC).
     * URL: POST /api/payme/callback
     */
    public function callback(Request $request): JsonResponse
    {
        if (! $this->payme->authenticate($request->header('Authorization'))) {
            return $this->error(PaymeException::INVALID_AUTH, 'Avtorizatsiya xatosi', $request->input('id'));
        }

        $method = $request->input('method');
        $params = $request->input('params', []);
        $id = $request->input('id');

        try {
            $result = match ($method) {
                'CheckPerformTransaction' => $this->checkPerform($params),
                'CreateTransaction' => $this->createTransaction($params),
                'PerformTransaction' => $this->performTransaction($params),
                'CancelTransaction' => $this->cancelTransaction($params),
                'CheckTransaction' => $this->checkTransaction($params),
                'GetStatement' => $this->getStatement($params),
                default => throw new PaymeException(PaymeException::METHOD_NOT_FOUND, 'Metod topilmadi'),
            };

            return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
        } catch (PaymeException $e) {
            return response()->json($e->toRpc($id));
        }
    }

    protected function resolveOrder(array $params): Order
    {
        $field = config('services.payme.account_field', 'order_id');
        $orderId = $params['account'][$field] ?? null;

        $order = $orderId ? Order::find($orderId) : null;

        if (! $order) {
            throw new PaymeException(PaymeException::ACCOUNT_NOT_FOUND, 'Buyurtma topilmadi', [
                'field' => "account.{$field}",
            ]);
        }

        return $order;
    }

    protected function assertAmount(Order $order, int $amount): void
    {
        if ((int) round($order->total * 100) !== (int) $amount) {
            throw new PaymeException(PaymeException::INVALID_AMOUNT, 'Summa noto\'g\'ri');
        }
    }

    protected function checkPerform(array $params): array
    {
        $order = $this->resolveOrder($params);
        $this->assertAmount($order, $params['amount'] ?? 0);

        if (! in_array($order->payment_status, ['pending', 'failed'])) {
            throw new PaymeException(PaymeException::ORDER_NOT_PAYABLE, 'Buyurtmani to\'lab bo\'lmaydi');
        }

        return ['allow' => true];
    }

    protected function createTransaction(array $params): array
    {
        $order = $this->resolveOrder($params);
        $this->assertAmount($order, $params['amount'] ?? 0);

        $payment = Payment::where('transaction_id', $params['id'])->first();

        if ($payment) {
            if ($payment->payme_state !== Payment::STATE_CREATED) {
                throw new PaymeException(PaymeException::CANT_PERFORM, 'Tranzaksiya holati noto\'g\'ri');
            }

            return [
                'create_time' => $payment->create_time,
                'transaction' => (string) $payment->id,
                'state' => $payment->payme_state,
            ];
        }

        // Boshqa tranzaksiya ushbu buyurtma uchun ochiq emasligini tekshirish
        $existing = Payment::where('order_id', $order->id)
            ->where('payme_state', Payment::STATE_CREATED)
            ->exists();

        if ($existing) {
            throw new PaymeException(PaymeException::ORDER_NOT_PAYABLE, 'Buyurtma uchun ochiq tranzaksiya mavjud');
        }

        $createTime = (int) ($params['time'] ?? round(microtime(true) * 1000));

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'payme',
            'transaction_id' => $params['id'],
            'amount' => $params['amount'],
            'state' => 'pending',
            'payme_state' => Payment::STATE_CREATED,
            'create_time' => $createTime,
        ]);

        return [
            'create_time' => $payment->create_time,
            'transaction' => (string) $payment->id,
            'state' => $payment->payme_state,
        ];
    }

    protected function performTransaction(array $params): array
    {
        $payment = $this->findPayment($params['id']);

        if ($payment->payme_state === Payment::STATE_COMPLETED) {
            return [
                'transaction' => (string) $payment->id,
                'perform_time' => $payment->perform_time,
                'state' => $payment->payme_state,
            ];
        }

        if ($payment->payme_state !== Payment::STATE_CREATED) {
            throw new PaymeException(PaymeException::CANT_PERFORM, 'Tranzaksiyani amalga oshirib bo\'lmaydi');
        }

        $performTime = (int) round(microtime(true) * 1000);

        $payment->update([
            'payme_state' => Payment::STATE_COMPLETED,
            'state' => 'paid',
            'perform_time' => $performTime,
        ]);

        $payment->order->update(['payment_status' => 'paid']);
        $payment->order->logStatus($payment->order->status, null, 'Payme orqali to\'landi');

        return [
            'transaction' => (string) $payment->id,
            'perform_time' => $performTime,
            'state' => $payment->payme_state,
        ];
    }

    protected function cancelTransaction(array $params): array
    {
        $payment = $this->findPayment($params['id']);

        $cancelTime = (int) round(microtime(true) * 1000);

        if (in_array($payment->payme_state, [Payment::STATE_CANCELLED, Payment::STATE_CANCELLED_AFTER_COMPLETE])) {
            return [
                'transaction' => (string) $payment->id,
                'cancel_time' => $payment->cancel_time,
                'state' => $payment->payme_state,
            ];
        }

        $newState = $payment->payme_state === Payment::STATE_COMPLETED
            ? Payment::STATE_CANCELLED_AFTER_COMPLETE
            : Payment::STATE_CANCELLED;

        $payment->update([
            'payme_state' => $newState,
            'state' => 'cancelled',
            'reason' => $params['reason'] ?? null,
            'cancel_time' => $cancelTime,
        ]);

        $payment->order->update(['payment_status' => 'failed']);

        return [
            'transaction' => (string) $payment->id,
            'cancel_time' => $cancelTime,
            'state' => $newState,
        ];
    }

    protected function checkTransaction(array $params): array
    {
        $payment = $this->findPayment($params['id']);

        return [
            'create_time' => $payment->create_time ?? 0,
            'perform_time' => $payment->perform_time ?? 0,
            'cancel_time' => $payment->cancel_time ?? 0,
            'transaction' => (string) $payment->id,
            'state' => $payment->payme_state,
            'reason' => $payment->reason,
        ];
    }

    protected function getStatement(array $params): array
    {
        $payments = Payment::where('provider', 'payme')
            ->whereBetween('create_time', [$params['from'] ?? 0, $params['to'] ?? PHP_INT_MAX])
            ->get();

        return [
            'transactions' => $payments->map(fn (Payment $p) => [
                'id' => $p->transaction_id,
                'time' => $p->create_time,
                'amount' => (int) $p->amount,
                'account' => [config('services.payme.account_field') => $p->order_id],
                'create_time' => $p->create_time ?? 0,
                'perform_time' => $p->perform_time ?? 0,
                'cancel_time' => $p->cancel_time ?? 0,
                'transaction' => (string) $p->id,
                'state' => $p->payme_state,
                'reason' => $p->reason,
            ])->all(),
        ];
    }

    protected function findPayment(string $transactionId): Payment
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (! $payment) {
            throw new PaymeException(PaymeException::TRANSACTION_NOT_FOUND, 'Tranzaksiya topilmadi');
        }

        return $payment;
    }

    protected function error(int $code, string $message, $id): JsonResponse
    {
        return response()->json((new PaymeException($code, $message))->toRpc($id));
    }
}
