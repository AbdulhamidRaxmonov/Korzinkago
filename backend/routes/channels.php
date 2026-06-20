<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/**
 * order.{orderId} — faqat buyurtma egasi yoki biriktirilgan kuryer eshita oladi.
 */
Broadcast::channel('order.{orderId}', function ($user, int $orderId) {
    $order = Order::find($orderId);

    if (! $order) {
        return false;
    }

    return $order->user_id === $user->id || $order->courier_id === $user->id;
});
