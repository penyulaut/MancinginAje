<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MidtransService;
use App\Models\Orders;
use App\Models\Order_items;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->middleware('auth');
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        $items = [];
        $total = 0;
        $productIds = array_keys($cart);
        $products = Products::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $productId => $data) {
            $product = $products->get($productId);
            if (!$product) continue;
            $quantity = (int) ($data['quantity'] ?? 1);
            $items[] = ['product' => $product, 'quantity' => $quantity, 'price' => $product->harga];
            $total += $product->harga * $quantity;
        }

        return view('pages.payment', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:1000',
            'payment_method' => 'required|string|in:bank_transfer,e_wallet,card',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        DB::beginTransaction();
        try {
            // load products and calculate total (stock already reserved when added to cart)
            $productIds = array_keys($cart);
            $products = Products::whereIn('id', $productIds)->get()->keyBy('id');

            $total = 0;
            foreach ($cart as $productId => $data) {
                $product = $products->get($productId);
                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan: ' . $productId);
                }

                $quantity = (int) ($data['quantity'] ?? 1);
                $total += $product->harga * $quantity;
            }

            // create order
            $order = Orders::create([
                'user_id' => Auth::id(),
                'total_harga' => $total,
                'status' => 'pending',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
            ]);

            // create order items (stock already decremented when added to cart)
            foreach ($cart as $productId => $data) {
                $product = $products->get($productId);
                $quantity = (int) ($data['quantity'] ?? 1);

                Order_items::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->harga,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat order: ' . $e->getMessage());
        }

        try {
            $snapToken = $this->midtrans->createSnapToken($order);
        } catch (\Exception $e) {
            return back()->with('error', 'Midtrans error: ' . $e->getMessage());
        }

        return view('pages.payment-checkout', compact('order', 'snapToken'));
    }

    public function finish(Request $request)
    {
        // Show success page; keep it simple
        session()->forget('cart');
        return view('pages.payment-success');
    }

    public function notification(Request $request)
    {
        $payload = json_decode($request->getContent());
        try {
            $this->midtrans->handleNotification($payload);
            return response('OK', 200);
        } catch (\Exception $e) {
            return response('Error', 500);
        }
    }
}
