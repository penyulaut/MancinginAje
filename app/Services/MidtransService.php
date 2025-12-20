<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');

        // Determine production flag.
        // Priority: explicit `mode` ('production'|'sandbox') -> `is_production` boolean
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
            $snapToken = Snap::getSnapToken($payload);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    /**
     * Create a core-api charge for offline channels (VA / cstore / qris)
     * Returns the Midtrans response object from Transaction::charge
     */
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

        // map selected method to core-api params
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
            // Fallback to snap token for other methods (credit card, ewallet)
            try {
                $snapToken = Snap::getSnapToken($params);
                return (object) ['snap_token' => $snapToken];
            } catch (\Exception $e) {
                throw new \Exception('Midtrans Snap Error: ' . $e->getMessage());
            }
        }

        try {
            $response = CoreApi::charge($params);
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Charge Error: ' . $e->getMessage());
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

    /**
     * Refresh order status by querying Midtrans transaction status and applying same mapping
     * used for notifications. Returns true if updated, false otherwise.
     */
    public function refreshOrderStatus($order)
    {
        // Determine transaction identifier to query
        $txId = $order->transaction_id ?? $order->snap_token ?? null;
        if (empty($txId)) {
            return false;
        }

        try {
            $resp = $this->getTransactionStatus($txId);
        } catch (\Exception $e) {
            \Log::error('Midtrans refresh error: ' . $e->getMessage(), ['order_id' => $order->id, 'tx' => $txId]);
            return false;
        }

        // $resp may be an object or array; normalize to object
        $notification = is_array($resp) ? (object) $resp : $resp;

        // Some responses contain 'order_id' in format "{orderId}-{timestamp}" similar to notifications
        $transactionStatus = $notification->transaction_status ?? ($notification->status ?? null);
        $fraudStatus = $notification->fraud_status ?? null;

        if (!$transactionStatus) {
            return false;
        }

        // Apply same mappings as handleNotification
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

        $order->transaction_id = $notification->transaction_id ?? $order->transaction_id;
        $order->save();

        try {
            event(new \App\Events\OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
        } catch (\Throwable $e) {
            \Log::error('Broadcast error for refresh', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return true;
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

        // Broadcast order update so frontend can react in real-time.
        try {
            event(new \App\Events\OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
        } catch (\Throwable $e) {
            \Log::error('Broadcast error for order update', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return true;
    }
}
