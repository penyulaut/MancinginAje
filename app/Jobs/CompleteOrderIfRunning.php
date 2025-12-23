<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Orders;
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Log;

class CompleteOrderIfRunning implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        // This job should be unique-ish when dispatched with delay;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $order = Orders::find($this->orderId);
            if (!$order) return;

            // Complete order if payment is confirmed but status not yet completed
            if ($order->payment_status === 'paid' && $order->status !== 'completed') {
                $order->status = 'completed';
                $order->save();

                try {
                    event(new OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
                } catch (\Throwable $e) {
                    Log::error('Broadcast error while completing order', ['error'=>$e->getMessage(),'order_id'=>$order->id]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error in CompleteOrderIfRunning job', ['error' => $e->getMessage(), 'order_id' => $this->orderId]);
        }
    }
}
