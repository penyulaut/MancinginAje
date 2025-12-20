<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CancelOrderIfUnpaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $order = \App\Models\Orders::with('items.product')->find($this->orderId);
            if (!$order) return;

            // Only cancel if still pending/unpaid
            if ($order->status !== 'pending') return;
            if (!in_array($order->payment_status, [null, 'pending', 'expired', 'failed'])) return;

            DB::transaction(function() use ($order) {
                // restore stock
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product && isset($product->stok)) {
                        $product->increment('stok', $item->quantity);
                    }
                }

                $order->status = 'cancelled';
                $order->payment_status = 'failed';
                $order->save();
            });

            try {
                event(new \App\Events\OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
            } catch (\Throwable $e) {
                Log::error('Broadcast error in CancelOrderIfUnpaid', ['error'=>$e->getMessage(),'order_id'=>$order->id]);
            }
        } catch (\Throwable $e) {
            Log::error('CancelOrderIfUnpaid failed', ['error'=>$e->getMessage(),'order_id'=>$this->orderId]);
        }
    }
}
