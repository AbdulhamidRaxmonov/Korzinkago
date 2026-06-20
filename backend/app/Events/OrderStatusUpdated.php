<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->order->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'number' => $this->order->number,
            'status' => $this->order->status,
            'payment_status' => $this->order->payment_status,
        ];
    }
}
