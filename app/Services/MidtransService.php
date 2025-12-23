<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');

        $mode = config('midtrans.mode');
        if ($mode === 'production') {
            $isProduction = true;
        } elseif ($mode === 'sandbox') {
            $isProduction = false;
        } else {
            $isProduction = (bool) config('midtrans.is_production');
        }

        Config::$isProduction = $isProduction;
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
            $start = Carbon::now('Asia/Jakarta');
            $payload['expiry'] = [
                'start_time' => $start->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => 5,
            ];
        } catch (\Throwable $e) {
            Log::warning('Midtrans expiry not set: ' . $e->getMessage());
        }

        try {
            $snapToken = Snap::getSnapToken($payload);
            return $snapToken;
        } catch (\Throwable $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function createCharge($order, $method)
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

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        if (str_starts_with($method, 'bank_transfer_')) {
            $bank = str_replace('bank_transfer_', '', $method);
            $params['payment_type'] = 'bank_transfer';
            $params['bank_transfer'] = ['bank' => $bank];
        } elseif ($method === 'echannel') {
            $params['payment_type'] = 'echannel';
        } elseif (str_starts_with($method, 'cstore_')) {
            $store = str_replace('cstore_', '', $method);
            $params['payment_type'] = 'cstore';
            $params['cstore'] = [
                'store' => $store,
                'message' => 'Pembayaran di ' . strtoupper($store),
            ];
        } elseif ($method === 'qris') {
            $params['payment_type'] = 'qris';
        } else {
            try {
                $snapToken = Snap::getSnapToken($params);
                return (object) ['snap_token' => $snapToken];
            } catch (\Throwable $e) {
                throw new \Exception('Midtrans Snap Error: ' . $e->getMessage());
            }
        }

        try {
            $response = CoreApi::charge($params);
            return $response;
        } catch (\Throwable $e) {
            throw new \Exception('Midtrans Charge Error: ' . $e->getMessage());
        }
    }

    public function getTransactionStatus($transactionId)
    {
        try {
            return Transaction::status($transactionId);
        } catch (\Throwable $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function refreshOrderStatus($order)
    {
        $txId = $order->transaction_id ?? $order->snap_token ?? null;
        if (empty($txId)) return false;

        try {
            $resp = $this->getTransactionStatus($txId);
        } catch (\Throwable $e) {
            Log::error('Midtrans refresh error: ' . $e->getMessage(), ['order_id' => $order->id, 'tx' => $txId]);
            return false;
        }

        $notification = is_array($resp) ? (object) $resp : $resp;
        $transactionStatus = $notification->transaction_status ?? ($notification->status ?? null);
        $fraudStatus = $notification->fraud_status ?? null;

        if (!$transactionStatus) return false;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $order->payment_status = 'pending';
                $order->status = 'pending';
            } else {
                $order->payment_status = 'paid';
                $order->status = 'completed';
            }
        } elseif ($transactionStatus === 'settlement') {
            $order->payment_status = 'paid';
            $order->status = 'completed';
        } elseif ($transactionStatus === 'pending') {
            $order->payment_status = 'pending';
            $order->status = 'pending';
        } elseif ($transactionStatus === 'deny') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        } elseif ($transactionStatus === 'expire') {
            $order->payment_status = 'expired';
            $order->status = 'cancelled';
        } elseif ($transactionStatus === 'cancel') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        }

        $order->transaction_id = $notification->transaction_id ?? $order->transaction_id;
        $order->save();

        try {
            event(new \App\Events\OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
        } catch (\Throwable $e) {
            Log::error('Broadcast error for refresh', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return true;
    }

    public function handleNotification($notification)
    {
        if (is_object($notification) && property_exists($notification, 'signature_key')) {
            $serverKey = config('midtrans.server_key');
            $orderId = $notification->order_id ?? '';
            $statusCode = $notification->status_code ?? '';
            $grossAmount = $notification->gross_amount ?? '';

            $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if (!hash_equals($expected, $notification->signature_key)) {
                Log::warning('Midtrans webhook signature mismatch', ['expected' => $expected, 'received' => $notification->signature_key, 'order_id' => $orderId]);
                return false;
            }
        }

        $orderId = explode('-', $notification->order_id)[0] ?? null;
        if (!$orderId) return false;

        $order = \App\Models\Orders::find($orderId);
        if (!$order) return false;

        $transactionStatus = $notification->transaction_status ?? null;
        $fraudStatus = $notification->fraud_status ?? null;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $order->payment_status = 'pending';
                $order->status = 'pending';
            } else {
                $order->payment_status = 'paid';
                $order->status = 'completed';
            }
        } elseif ($transactionStatus === 'settlement') {
            $order->payment_status = 'paid';
            $order->status = 'completed';
        } elseif ($transactionStatus === 'pending') {
            $order->payment_status = 'pending';
            $order->status = 'pending';
        } elseif ($transactionStatus === 'deny') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        } elseif ($transactionStatus === 'expire') {
            $order->payment_status = 'expired';
            $order->status = 'cancelled';
        } elseif ($transactionStatus === 'cancel') {
            $order->payment_status = 'failed';
            $order->status = 'cancelled';
        }

        $order->transaction_id = $notification->transaction_id ?? $order->transaction_id;
        $order->save();

        try {
            event(new \App\Events\OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
        } catch (\Throwable $e) {
            Log::error('Broadcast error for order update', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return true;
    }
}
