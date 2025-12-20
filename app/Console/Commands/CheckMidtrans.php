<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckMidtrans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:check {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Midtrans transaction status for given order id';

    public function handle()
    {
        $orderId = $this->argument('orderId');

        try {
            $order = \App\Models\Orders::with('items.product')->find($orderId);
        } catch (\Throwable $e) {
            $this->error('DB Error: ' . $e->getMessage());
            return 1;
        }

        if (!$order) {
            $this->info('ORDER_NOT_FOUND');
            return 0;
        }

        $this->info('ORDER: ' . json_encode([
            'id' => $order->id,
            'transaction_id' => $order->transaction_id,
            'snap_token' => $order->snap_token,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
        ]));

        $txId = $order->transaction_id ?? ($order->snap_token ?? '');
        if (empty($txId)) {
            $this->info('NO_TRANSACTION_ID_OR_SNAP_TOKEN');
            return 0;
        }

        try {
            $service = app()->make(\App\Services\MidtransService::class);
            $status = $service->getTransactionStatus($txId);
            $this->info('MIDTRANS_STATUS: ' . json_encode($status));
        } catch (\Throwable $e) {
            $this->error('MIDTRANS_ERROR: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
