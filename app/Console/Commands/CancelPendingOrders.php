<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancelPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-pending {--minutes=5 : Age in minutes after which pending orders are cancelled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel unpaid orders older than configured minutes (default 5 minutes) and restore stock';

    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $threshold = Carbon::now()->subMinutes($minutes);

        $query = \App\Models\Orders::where('status', 'pending')
            ->where(function($q){
                $q->whereNull('payment_status')->orWhere('payment_status', 'pending');
            })
            ->where('created_at', '<=', $threshold);

        $orders = $query->get();
        $count = $orders->count();

        if ($count === 0) {
            $this->info('No pending orders older than ' . $minutes . ' minutes.');
            return 0;
        }

        $this->info('Cancelling ' . $count . ' order(s) older than ' . $minutes . ' minutes...');

        foreach ($orders as $order) {
            try {
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
                    \Log::error('Broadcast error in CancelPendingOrders', ['error'=>$e->getMessage(),'order_id'=>$order->id]);
                }

                $this->info('Cancelled order #' . $order->id);
            } catch (\Throwable $e) {
                \Log::error('Failed to cancel pending order', ['error'=>$e->getMessage(),'order_id'=>$order->id]);
                $this->error('Failed to cancel order #' . $order->id . ': ' . $e->getMessage());
            }
        }

        return 0;
    }
}
