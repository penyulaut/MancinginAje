<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Orders;
use App\Models\Order_items;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{

    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['quantity'];
        }    

        return view('pages.checkout', compact('cart', 'total'));
    }

    public function chekout()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['quantity'];
        }    

        // Simpan data order ke database
        $order = Orders::create([
            'user_id' => Auth::id(),
            'total_harga' => $total,
            'status' => 'pending',
        ]);

        // Simpan data item order ke database
        foreach ($cart as $productId => $item) {
            Order_items::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['harga'],
            ]);
        }

        // Kosongkan keranjang setelah checkout
        session()->forget('cart');

        return redirect()->route('pages.beranda')->with('success', 'Order placed successfully!');
    }

    public function showorders()
    {
        $user = Auth::user();
        $baseQuery = Orders::where('user_id', $user->id)->with('items.product')->latest();

        // Group orders
        $ordersAll = $baseQuery->get();

        // Refresh statuses for orders that may still be pending/expired/failed
        $midtrans = app()->make(\App\Services\MidtransService::class);
        foreach ($ordersAll as $ord) {
            if (in_array($ord->payment_status, [null,'pending','expired','failed']) && ($ord->transaction_id || $ord->snap_token)) {
                try {
                    $midtrans->refreshOrderStatus($ord);
                    // re-load refreshed attributes
                    $ord->refresh();
                } catch (\Throwable $e) {
                    \Log::error('Error refreshing order status in listing', ['error'=>$e->getMessage(),'order_id'=>$ord->id]);
                }
            }
        }

        $ordersUnpaid = $ordersAll->filter(function($o) {
            return ($o->payment_status === null || $o->payment_status === 'pending' || $o->payment_status === 'expired' || $o->payment_status === 'failed') && $o->status === 'pending';
        });

        $ordersRunning = $ordersAll->filter(function($o) {
            // 'pesanan berjalan' = paid but not completed
            return $o->status === 'paid' && ($o->payment_status === 'paid');
        });

        $ordersCompleted = $ordersAll->filter(function($o) {
            return $o->status === 'completed';
        });

        $ordersCancelled = $ordersAll->filter(function($o) {
            return $o->status === 'cancelled' || ($o->payment_status !== null && in_array($o->payment_status, ['failed','expired']));
        });

        return view('pages.YourOrders', [
            'ordersAll' => $ordersAll,
            'ordersUnpaid' => $ordersUnpaid,
            'ordersRunning' => $ordersRunning,
            'ordersCompleted' => $ordersCompleted,
            'ordersCancelled' => $ordersCancelled,
        ]);
    }

    /**
     * Cancel an unpaid order (mark as cancelled).
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $order = Orders::where('id', $id)->where('user_id', $user->id)->first();
        if (!$order) {
            return redirect()->route('pages.yourorders')->with('error', 'Order tidak ditemukan.');
        }

        // Only allow cancel for pending/unpaid orders
        if ($order->status !== 'pending' || ($order->payment_status !== null && $order->payment_status === 'paid')) {
            return redirect()->route('pages.yourorders')->with('info', 'Order tidak dapat dibatalkan.');
        }

        $order->status = 'cancelled';
        $order->payment_status = 'failed';
        $order->save();

        // Broadcast update
        try {
            event(new OrderStatusUpdated($order->id, $order->status, $order->payment_status, $order->transaction_id));
        } catch (\Throwable $e) {
            \Log::error('Broadcast error on cancel', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return redirect()->route('pages.yourorders')->with('success', 'Pesanan telah dibatalkan.');
    }
}
