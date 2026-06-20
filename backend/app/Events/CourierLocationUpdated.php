<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orderId,
        public float $lat,
        public float $lng,
    ) {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->orderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'courier.location';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
