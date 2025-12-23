<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MidtransService;
use App\Models\Orders;
use App\Models\Order_items;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\CancelOrderIfUnpaid;

class PaymentController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->middleware('auth')->except('notification');
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        // Admins are not allowed to make purchases
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($currentUser && ($currentUser->role ?? '') === 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Admin tidak dapat melakukan pembelian.');
        }

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
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if ($currentUser && ($currentUser->role ?? '') === 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Admin tidak dapat melakukan pembelian.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:1000',
            'payment_method' => 'required|string|in:bank_transfer_bca,bank_transfer_bni,bank_transfer_bri,bank_transfer_permata,bank_transfer_mandiri,echannel,credit_card,gopay,shopeepay,dana,qris,cstore_alfamart,cstore_indomaret,akulaku',
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
            // Dispatch a delayed job to auto-cancel if still unpaid after 5 minutes
            try {
                CancelOrderIfUnpaid::dispatch($order->id)->delay(now()->addMinutes(5));
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch CancelOrderIfUnpaid job', ['error' => $e->getMessage(), 'order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat order: ' . $e->getMessage());
        }

        try {
            // for offline channels (VA / cstore / qris) we want the charge response
            $method = $validated['payment_method'];
            $chargeResp = $this->midtrans->createCharge($order, $method);

            // If createCharge returned a snap token (object with snap_token), show snap checkout
            if (is_object($chargeResp) && property_exists($chargeResp, 'snap_token')) {
                $snapToken = $chargeResp->snap_token;
                $order->snap_token = $snapToken;
                $order->save();
                return view('pages.payment-checkout', compact('order', 'snapToken'));
            }

            // Otherwise, it's a core charge response (bank VA, cstore, qris)
            $order->transaction_id = $chargeResp->transaction_id ?? null;
            // keep a simple reference in snap_token too if available
            if (isset($chargeResp->va_numbers) && is_array($chargeResp->va_numbers) && count($chargeResp->va_numbers) > 0) {
                $order->snap_token = $chargeResp->va_numbers[0]->va_number ?? $order->snap_token;
            }
            $order->save();

            return view('pages.payment-instructions', ['order' => $order, 'midtrans' => $chargeResp, 'method' => $method]);
        } catch (\Exception $e) {
            return back()->with('error', 'Midtrans error: ' . $e->getMessage());
        }
    }

    public function finish(Request $request)
    {
        // Show success page; load order if provided
        $orderId = $request->query('order_id');
        if ($orderId) {
            $order = Orders::with('items.product')->find($orderId);
            if ($order) {
                try {
                    $this->midtrans->refreshOrderStatus($order);
                    $order->refresh();
                } catch (\Throwable $e) {
                    Log::error('Error refreshing order status on finish', ['error' => $e->getMessage(), 'order_id' => $order->id]);
                }
            }
        } else {
            $order = null;
        }

        // Only clear cart and show success when payment_status is paid.
        if ($order && $order->payment_status === 'paid') {
            session()->forget('cart');
            return view('pages.payment-success', compact('order'));
        }

        // If not paid yet, redirect to payment instructions / pending page
        return redirect()->route('pages.yourorders')->with('info', 'Pembayaran belum terkonfirmasi. Menunggu notifikasi dari Midtrans.');
    }

    /**
     * Retry payment for an existing order (re-initiate Midtrans charge/snap)
     */
    public function retry(Request $request, $id)
    {
        $order = Orders::with('items.product')->find($id);
        if (!$order) {
            return redirect()->route('pages.yourorders')->with('error', 'Order tidak ditemukan.');
        }

        // Ensure the authenticated user owns the order
        if ($order->user_id !== Auth::id()) {
            return redirect()->route('pages.yourorders')->with('error', 'Anda tidak berhak mengakses order ini.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('pages.yourorders')->with('info', 'Order sudah dibayar.');
        }

        try {
            $method = $order->payment_method ?? 'credit_card';
            $chargeResp = $this->midtrans->createCharge($order, $method);

            if (is_object($chargeResp) && property_exists($chargeResp, 'snap_token')) {
                $snapToken = $chargeResp->snap_token;
                $order->snap_token = $snapToken;
                $order->save();
                return view('pages.payment-checkout', compact('order', 'snapToken'));
            }

            // core charge response (bank VA, cstore, qris)
            $order->transaction_id = $chargeResp->transaction_id ?? $order->transaction_id;
            if (isset($chargeResp->va_numbers) && is_array($chargeResp->va_numbers) && count($chargeResp->va_numbers) > 0) {
                $order->snap_token = $chargeResp->va_numbers[0]->va_number ?? $order->snap_token;
            }
            $order->save();

            return view('pages.payment-instructions', ['order' => $order, 'midtrans' => $chargeResp, 'method' => $method]);
        } catch (\Exception $e) {
            return redirect()->route('pages.yourorders')->with('error', 'Midtrans error: ' . $e->getMessage());
        }
    }

    /**
     * Return order status JSON for polling.
     */
    public function orderStatus(Request $request, $id)
    {
        $order = Orders::with('items.product')->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        // Only owner or admin may poll
        $user = $request->user();
        if (!$user || ($order->user_id !== $user->id && ($user->role ?? '') !== 'admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Attempt to refresh status from Midtrans to provide up-to-date info
        try {
            $this->midtrans->refreshOrderStatus($order);
            // reload model after potential update
            $order->refresh();
        } catch (\Throwable $e) {
            // don't fail the poll entirely; log and return existing state
            Log::warning('Failed to refresh order status during poll', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'transaction_id' => $order->transaction_id,
        ]);
    }

    /**
     * Clear the user's cart server-side if order is confirmed paid.
     * Returns JSON { cleared: bool, payment_status: string }
     */
    public function clearCartIfPaid(Request $request, $id)
    {
        $order = Orders::with('items.product')->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        $user = $request->user();
        if (!$user || ($order->user_id !== $user->id && ($user->role ?? '') !== 'admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->midtrans->refreshOrderStatus($order);
            $order->refresh();
        } catch (\Throwable $e) {
            Log::warning('Failed to refresh order status during clearCartIfPaid', ['error' => $e->getMessage(), 'order_id' => $order->id]);
        }

        if ($order->payment_status === 'paid') {
            // clear cart for this session
            try {
                session()->forget('cart');
            } catch (\Throwable $e) {
                Log::warning('Failed to clear cart after payment', ['error' => $e->getMessage()]);
            }
            return response()->json(['cleared' => true, 'payment_status' => $order->payment_status]);
        }

        return response()->json(['cleared' => false, 'payment_status' => $order->payment_status]);
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
