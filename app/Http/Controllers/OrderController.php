<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Order_items;

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
            'user_id' => auth()->id(),
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

    public function showOrders()
    {
        $orders = \App\Models\Orders::with('items.product')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('pages.YourOrders', compact('orders'));
    }
}
