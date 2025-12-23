<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Products;
use App\Models\Orders;
use App\Models\Category;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user && in_array($user->role, ['seller','admin'])) {
            // Dashboard for sellers - show their products and orders
            $products = Products::where('seller_id', $user->id)->get();
            $totalProducts = $products->count();
            $totalOrders = Orders::whereHas('items.product', function($q) use ($user){
                $q->where('seller_id', $user->id);
            })->count();

            return view('dashboard.index', [
                'products' => $products,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
            ]);
        }

        // Default to customer dashboard
        return redirect()->route('pages.beranda');
    }

    /**
     * Admin dashboard: show all products and quick restock UI
     */
    public function indexAdmin(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        // Products list
        $products = Products::with('category', 'seller')->get();
        $totalProducts = $products->count();

        // Transactions / orders
        $totalTransactions = Orders::count();
        $recentTransactions = Orders::with('user', 'items.product')->orderBy('created_at', 'desc')->limit(10)->get();

        // If the admin requests the full transactions tab, load all orders (paginated)
        $activeTab = $request->input('tab', 'products');
        $allOrders = null;
        if ($activeTab === 'transactions') {
            $sort = $request->input('sort', 'date_desc');
            $statusFilter = $request->input('status');

            $ordersQuery = Orders::with('user', 'items.product');
            if ($statusFilter) {
                $ordersQuery->where('status', $statusFilter);
            }

            if ($sort === 'date_asc') {
                $ordersQuery->orderBy('created_at', 'asc');
            } else {
                $ordersQuery->orderBy('created_at', 'desc');
            }

            $allOrders = $ordersQuery->paginate(25)->appends($request->only(['sort','status']));
        }

        // Transaction status breakdown
        $transactionsPending = Orders::where('status', 'pending')->count();
        // Count completed orders as paid revenue
        $transactionsPaid = Orders::where('status', 'completed')->count();
        $transactionsCancelled = Orders::where('status', 'cancelled')->orWhere('status', 'failed')->count();

        // Users and revenue
        $totalUsers = \App\Models\User::count();
        $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->limit(10)->get();
        $totalRevenue = Orders::where('payment_status', 'paid')->sum('total_harga');

        return view('dashboard.index', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'totalTransactions' => $totalTransactions,
            'recentTransactions' => $recentTransactions,
            'totalUsers' => $totalUsers,
            'totalRevenue' => $totalRevenue,
            'transactionsPending' => $transactionsPending,
            'transactionsPaid' => $transactionsPaid,
            'transactionsCancelled' => $transactionsCancelled,
            'recentUsers' => $recentUsers,
            'activeTab' => $activeTab,
            'allOrders' => $allOrders,
        ]);
    }

    /**
     * Restock a product (admin only). Accepts `add_stock` integer.
     */
    public function restock(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'add_stock' => 'required|integer|min:1',
        ]);

        $product = Products::findOrFail($id);
        $product->stok = (int) $product->stok + (int) $validated['add_stock'];
        $product->save();

        return back()->with('success', 'Stok berhasil ditambahkan.');
    }

    /**
     * Laporan belanja (admin only) - filter by period and category
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $period = $request->input('period', 'monthly'); // daily|monthly|yearly
        $categoryId = $request->input('category_id');
        $dateInput = $request->input('date');

        // Determine date range
        $now = Carbon::now();
        try {
            $ref = $dateInput ? Carbon::parse($dateInput) : $now;
        } catch (\Exception $e) {
            $ref = $now;
        }

        if ($period === 'daily') {
            $start = $ref->copy()->startOfDay();
            $end = $ref->copy()->endOfDay();
        } elseif ($period === 'yearly') {
            $start = $ref->copy()->startOfYear();
            $end = $ref->copy()->endOfYear();
        } else { // monthly
            $start = $ref->copy()->startOfMonth();
            $end = $ref->copy()->endOfMonth();
        }

        $ordersQuery = Orders::with('items.product')->whereBetween('created_at', [$start, $end]);

        if ($categoryId) {
            $ordersQuery->whereHas('items.product', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();

        // aggregate totals
        $totalSales = $orders->sum('total_harga');
        $totalOrders = $orders->count();

        $categories = Category::all();

        // pass date strings to view to avoid calling methods on non-objects in blade
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        return view('dashboard.reports', compact('orders', 'totalSales', 'totalOrders', 'categories', 'period', 'dateInput', 'categoryId', 'startDate', 'endDate'));
    }

    /**
     * Admin: list all orders with status and items
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $status = $request->input('status'); // optional filter

        $query = Orders::with('user', 'items.product')->orderBy('created_at', 'desc');
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->get();

        return view('dashboard.orders', compact('orders', 'status'));
    }

    /**
     * Admin: cancel an order if not paid — restock items
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $order = Orders::with('items')->findOrFail($id);
        if ($order->status === 'completed') {
            return back()->with('error', 'Pesanan sudah dibayar, tidak dapat dibatalkan.');
        }

        // Restock products
        foreach ($order->items as $item) {
            $product = Products::find($item->product_id);
            if ($product) {
                $product->stok = (int) $product->stok + (int) $item->quantity;
                $product->save();
            }
        }

        $order->status = 'failed';
        $order->save();

        return back()->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }

    /**
     * Admin: accept (auto-approve) an order — mark as paid
     */
    public function acceptOrder(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $order = Orders::findOrFail($id);
        if ($order->status === 'completed') {
            return back()->with('info', 'Pesanan sudah berstatus dibayar.');
        }

        // Accept order: mark as paid/completed
        $order->status = 'completed';
        $order->payment_status = 'paid';
        $order->save();

        return back()->with('success', 'Pesanan disetujui (auto-acc).');
    }

    /**
     * Admin: delete a seller account and its products.
     */
    public function destroySeller(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $seller = \App\Models\User::findOrFail($id);
        if (($seller->role ?? '') !== 'seller') {
            return back()->with('error', 'Target bukan akun penjual.');
        }

        \DB::beginTransaction();
        try {
            // delete products belonging to seller
            Products::where('seller_id', $seller->id)->delete();
            // optionally, you may want to reassign or delete orders — here we keep orders but disassociate seller products

            $seller->delete();

            \DB::commit();
            return back()->with('success', 'Akun penjual dan produknya berhasil dihapus.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete seller: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus akun penjual.');
        }
    }

    /**
     * Admin: delete any user account (except self). Removes seller's products when applicable.
     */
    public function destroyUser(Request $request, $id)
    {
        $auth = Auth::user();
        if (!$auth || $auth->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        if ((int)$auth->id === (int)$id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $target = \App\Models\User::findOrFail($id);

        \DB::beginTransaction();
        try {
            if (($target->role ?? '') === 'seller') {
                // delete seller products
                Products::where('seller_id', $target->id)->delete();
            }

            $target->delete();

            \DB::commit();
            return back()->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete user: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus akun.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('dashboard.create', compact('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
