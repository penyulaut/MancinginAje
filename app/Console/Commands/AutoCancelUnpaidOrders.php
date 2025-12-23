<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Orders;
use App\Models\Order_items;
use App\Models\Products;
use Illuminate\Support\Facades\Log;

class AutoCancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * We'll expose this as `orders:cancel-pending` so Kernel can schedule it.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel orders with status pending older than 5 minutes and restock items.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subMinutes(5);

        $orders = Orders::with('items')->where('status', 'pending')->where('created_at', '<', $cutoff)->get();

        if ($orders->isEmpty()) {
            $this->info('No pending orders older than 5 minutes.');
            return 0;
        }

        foreach ($orders as $order) {
            try {
                // Restock each product in the order
                foreach ($order->items as $item) {
                    $product = Products::find($item->product_id);
                    if ($product) {
                        // Some tables use 'stok' for stock
                        if (isset($product->stok)) {
                            $product->stok = (int) $product->stok + (int) $item->quantity;
                        } elseif (isset($product->stock)) {
                            $product->stock = (int) $product->stock + (int) $item->quantity;
                        }
                        $product->save();
                    }
                }

                $order->status = 'cancelled';
                $order->save();

                $message = sprintf('Order #%s cancelled due to timeout (older than 5 minutes).', $order->id);
                Log::info($message, ['order_id' => $order->id]);
                $this->info($message);
            } catch (\Exception $e) {
                Log::error('Auto-cancel failed for order ' . $order->id . ': ' . $e->getMessage());
                $this->error('Failed to cancel order ' . $order->id . '.');
            }
        }

        return 0;
    }
}
