<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orders;
use Carbon\Carbon;

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
            // Mark as cancelled
            $order->status = 'cancelled';
            $order->save();
            $count++;
        }

        $this->info("Cancelled {$count} pending order(s) older than {$minutes} minutes.");

        return 0;
    }
}
