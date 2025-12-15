<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Order_items;
use App\Models\Products;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->midtransService = new MidtransService();
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['quantity'];
        }

        $user = Auth::user();
        
        return view('pages.payment', [
            'cart' => $cart,
            'total' => $total,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string|in:bank_transfer,e_wallet,card',
        ]);

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['quantity'];
        }

        try {
            // Create order
            $order = Orders::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'total_harga' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cart as $productId => $item) {
                Order_items::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['harga'],
                ]);
            }

            // Get snap token from Midtrans
            $snapToken = $this->midtransService->createSnapToken($order);

            return view('pages.payment-process', [
                'snapToken' => $snapToken,
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function finish(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = Orders::find($orderId);

        if (!$order) {
            return redirect()->route('pages.beranda')->with('error', 'Order tidak ditemukan');
        }

        if ($order->payment_status == 'paid') {
            // Clear cart
            session()->forget('cart');
            
            return view('pages.payment-success', [
                'order' => $order,
            ]);
        }

        return redirect()->route('cart.index')->with('error', 'Pembayaran belum berhasil');
    }

    public function notification(Request $request)
    {
        $notification = json_decode($request->getContent());

        try {
            $this->midtransService->handleNotification($notification);
            http_response_code(200);
        } catch (\Exception $e) {
            http_response_code(400);
        }
    }
}
