<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function __construct(protected FcmService $fcm)
    {
    }

    /**
     * Kuryer online/offline holatini o'zgartirish.
     */
    public function toggleOnline(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['is_online' => $request->boolean('is_online')]);

        return response()->json(['is_online' => $user->is_online]);
    }

    /**
     * Kuryer joriy lokatsiyasini yangilash (real-time tracking).
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $request->user()->update([
            'current_lat' => $data['lat'],
            'current_lng' => $data['lng'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mavjud (bo'sh) buyurtmalar — qabul qilish uchun.
     */
    public function available(Request $request): JsonResponse
    {
        $orders = Order::whereNull('courier_id')
            ->whereIn('status', ['ready', 'accepted', 'assembling'])
            ->with('items')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Kuryerning faol va tarixiy buyurtmalari.
     */
    public function myOrders(Request $request): JsonResponse
    {
        $active = Order::where('courier_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'assembling', 'ready', 'on_way'])
            ->with(['items', 'user:id,name,phone'])
            ->latest()
            ->get();

        $history = Order::where('courier_id', $request->user()->id)
            ->whereIn('status', ['delivered', 'cancelled'])
            ->with('items')
            ->latest()
            ->limit(50)
            ->get();

        return response()->json(['active' => $active, 'history' => $history]);
    }

    /**
     * Buyurtmani qabul qilish (kuryerga biriktirish).
     */
    public function accept(Request $request, Order $order): JsonResponse
    {
        if ($order->courier_id !== null) {
            return response()->json(['message' => 'Bu buyurtma allaqachon biriktirilgan.'], 422);
        }

        $order->update([
            'courier_id' => $request->user()->id,
            'status' => 'on_way',
            'accepted_at' => now(),
        ]);

        $order->logStatus('on_way', $request->user()->id, 'Kuryer qabul qildi');

        $this->fcm->sendToUser($order->user_id, 'Kuryer yo\'lda', "{$order->number} buyurtmangizni kuryer yetkazmoqda.", [
            'order_id' => $order->id,
            'type' => 'order_on_way',
        ]);

        return response()->json(['success' => true, 'order' => $order->load('items', 'user:id,name,phone')]);
    }

    /**
     * Buyurtma statusini yangilash (on_way -> delivered).
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        abort_if($order->courier_id !== $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:on_way,delivered'],
        ]);

        $order->update([
            'status' => $data['status'],
            'delivered_at' => $data['status'] === 'delivered' ? now() : null,
            'payment_status' => $data['status'] === 'delivered' && $order->payment_method === 'cash'
                ? 'paid'
                : $order->payment_status,
        ]);

        $order->logStatus($data['status'], $request->user()->id);

        if ($data['status'] === 'delivered') {
            $this->fcm->sendToUser($order->user_id, 'Buyurtma yetkazildi', "{$order->number} buyurtmangiz yetkazib berildi. Rahmat!", [
                'order_id' => $order->id,
                'type' => 'order_delivered',
            ]);
        }

        return response()->json(['success' => true, 'order' => $order]);
    }
}
