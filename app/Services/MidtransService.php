<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken($order)
    {
        $transactionDetails = [
            'order_id' => $order->id . '-' . time(),
            'gross_amount' => (int) $order->total_harga,
        ];

        $customerDetails = [
            'first_name' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
        ];

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => $item->product_id,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product->nama ?? 'Product',
            ];
        }

        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($payload);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function getTransactionStatus($transactionId)
    {
        try {
            $status = Transaction::status($transactionId);
            return $status;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function handleNotification($notification)
    {
        $orderId = explode('-', $notification->order_id)[0];
        $order = \App\Models\Orders::find($orderId);

        if (!$order) {
            return false;
        }

        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->payment_status = 'pending';
                $order->status = 'pending';
            } else if ($fraudStatus == 'accept') {
                $order->payment_status = 'paid';
                $order->status = 'paid';
            }
        } else if ($transactionStatus == 'settlement') {
            $order->payment_status = 'paid';
            $order->status = 'paid';
        } else if ($transactionStatus == 'pending') {
            $order->payment_status = 'pending';
            $order->status = 'pending';
        } else if ($transactionStatus == 'deny') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        } else if ($transactionStatus == 'expire') {
            $order->payment_status = 'expired';
            $order->status = 'cancelled';
        } else if ($transactionStatus == 'cancel') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        }

        $order->transaction_id = $notification->transaction_id;
        $order->save();

        return true;
    }
}
