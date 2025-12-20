<?php
$order = App\Models\Orders::with('items.product')->find(43);
if (!$order) { echo "ORDER_NOT_FOUND\n"; exit(0); }
echo "ORDER: " . json_encode(['id'=>$order->id,'transaction_id'=>$order->transaction_id,'snap_token'=>$order->snap_token,'payment_status'=>$order->payment_status,'payment_method'=>$order->payment_method]) . "\n";
try {
    $txId = $order->transaction_id ?? ($order->snap_token ?? '');
    if (empty($txId)) {
        echo "NO_TRANSACTION_ID_OR_SNAP_TOKEN\n";
    } else {
        $status = app()->make(App\Services\MidtransService::class)->getTransactionStatus($txId);
        echo "MIDTRANS_STATUS: " . json_encode($status) . "\n";
    }
} catch (Exception $e) {
    echo "MIDTRANS_ERROR: " . $e->getMessage() . "\n";
}
