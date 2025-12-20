<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $orderId;
    public string $status;
    public ?string $paymentStatus;
    public ?string $transactionId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $orderId, string $status, ?string $paymentStatus = null, ?string $transactionId = null)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->paymentStatus = $paymentStatus;
        $this->transactionId = $transactionId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Using a public channel `orders.{id}` for prototype. For production consider `private` channels.
     */
    public function broadcastOn()
    {
        return new Channel('orders.' . $this->orderId);
    }

    /**
     * Data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'payment_status' => $this->paymentStatus,
            'transaction_id' => $this->transactionId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }
}
