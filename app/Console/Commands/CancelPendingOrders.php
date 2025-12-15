<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orders;
use App\Models\Order_items;
use App\Models\Products;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancelPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending orders older than configured minutes (default: 5 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = 5;
        $threshold = Carbon::now()->subMinutes($minutes);

        $orders = Orders::where('status', 'pending')
            ->where('created_at', '<=', $threshold)
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            DB::transaction(function() use ($order, &$count) {
                // Restore stock for each item
                $items = $order->items()->get();
                foreach ($items as $item) {
                    $product = $item->product;
                    if ($product) {
                        // increase stok by item quantity
                        $product->increment('stok', $item->quantity);
                    }
                }

                // Mark order as cancelled
                $order->status = 'cancelled';
                $order->save();
                $count++;
            });
        }

        $this->info("Cancelled {$count} pending order(s) older than {$minutes} minutes.");

        return 0;
    }
}
