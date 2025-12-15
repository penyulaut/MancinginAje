<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $orders = Orders::where('user_id', $user->id)->with('items')->latest()->paginate(10);

        return view('pages.YourOrders', [
            'orders' => $orders,
        ]);
    }
}
