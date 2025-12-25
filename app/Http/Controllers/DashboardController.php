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

        $activeTab = $request->input('tab', 'dashboard');
        
        // Initialize variables
        $products = collect();
        $allOrders = null;
        $recentProducts = collect();
        $recentOrders = collect();
        $lowStockProducts = collect();
        $recentTransactions = collect();
        $recentUsers = collect();
        
        // Cache counts for 5 minutes to reduce DB load
        $totalProducts = \Illuminate\Support\Facades\Cache::remember('admin_total_products', 300, fn() => Products::count());
        $activeProducts = \Illuminate\Support\Facades\Cache::remember('admin_active_products', 300, fn() => Products::where('status', 'active')->count());
        $inactiveProducts = \Illuminate\Support\Facades\Cache::remember('admin_inactive_products', 300, fn() => Products::where('status', 'inactive')->orWhere('stok', '<=', 0)->count());
        $totalCategories = \Illuminate\Support\Facades\Cache::remember('admin_total_categories', 300, fn() => Category::count());
        $totalTransactions = \Illuminate\Support\Facades\Cache::remember('admin_total_transactions', 300, fn() => Orders::count());
        $totalUsers = \Illuminate\Support\Facades\Cache::remember('admin_total_users', 300, fn() => \App\Models\User::count());
        $totalRevenue = \Illuminate\Support\Facades\Cache::remember('admin_total_revenue', 300, fn() => Orders::where('payment_status', 'paid')->sum('total_harga'));
        $transactionsPending = \Illuminate\Support\Facades\Cache::remember('admin_pending', 300, fn() => Orders::where('status', 'pending')->count());
        $transactionsPaid = \Illuminate\Support\Facades\Cache::remember('admin_paid', 300, fn() => Orders::where('status', 'completed')->count());
        $transactionsCancelled = \Illuminate\Support\Facades\Cache::remember('admin_cancelled', 300, fn() => Orders::where('status', 'cancelled')->orWhere('status', 'failed')->count());

        // Load data based on active tab only
        if ($activeTab === 'dashboard') {
            // Only load what dashboard needs
            $recentProducts = Products::select('id', 'nama', 'gambar', 'harga', 'stok', 'category_id')
                ->with('category:id,nama')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $recentOrders = Orders::select('id', 'customer_name', 'user_id', 'total_harga', 'status', 'payment_status', 'created_at')
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $lowStockProducts = Products::select('id', 'nama', 'gambar', 'stok', 'category_id')
                ->where('stok', '<', 10)
                ->where('stok', '>', 0)
                ->with('category:id,nama')
                ->limit(4)
                ->get();
        } elseif ($activeTab === 'products') {
            $products = Products::select('id', 'nama', 'gambar', 'harga', 'stok', 'category_id', 'seller_id', 'status')
                ->with('category:id,nama', 'seller:id,name')
                ->get();
        } elseif ($activeTab === 'transactions') {
            $sort = $request->input('sort', 'date_desc');
            $statusFilter = $request->input('status');

            $ordersQuery = Orders::select('id', 'customer_name', 'user_id', 'total_harga', 'status', 'payment_status', 'created_at')
                ->with(['user:id,name,email', 'items:id,order_id,product_id,quantity', 'items.product:id,nama']);
            
            if ($statusFilter) {
                $ordersQuery->where('status', $statusFilter);
            }

            $ordersQuery->orderBy('created_at', $sort === 'date_asc' ? 'asc' : 'desc');
            $allOrders = $ordersQuery->paginate(10)->appends($request->only(['tab', 'sort', 'status']));
        } elseif ($activeTab === 'users') {
            $recentUsers = \App\Models\User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return view('dashboard.index', compact(
            'products', 'totalProducts', 'activeProducts', 'inactiveProducts',
            'totalCategories', 'lowStockProducts', 'recentProducts', 'recentOrders',
            'totalTransactions', 'recentTransactions', 'totalUsers', 'totalRevenue',
            'transactionsPending', 'transactionsPaid', 'transactionsCancelled',
            'recentUsers', 'activeTab', 'allOrders'
        ));
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
     * Admin: list only active/pending orders
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        // Only show pending orders (currently active orders)
        $orders = Orders::with('user', 'items.product')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.orders', compact('orders'));
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

    /**
     * Export transactions to XLSX format
     */
    public function exportTransactions(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Akses ditolak.');
        }

        $statusFilter = $request->input('status');
        $sort = $request->input('sort', 'date_desc');

        $query = Orders::with('user', 'items.product');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($sort === 'date_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->get();

        // Build CSV/XLSX data
        $filename = 'transactions_' . date('Y-m-d_His') . '.xlsx';
        
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['Order ID', 'Tanggal', 'Pelanggan', 'Email', 'Items', 'Total', 'Status', 'Payment Status'], ';');
            
            foreach ($orders as $order) {
                $items = $order->items->map(function($item) {
                    return ($item->product?->nama ?? 'Produk') . ' x' . $item->quantity;
                })->implode(', ');
                
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->customer_name ?? $order->user?->name ?? 'Guest',
                    $order->user?->email ?? '-',
                    $items,
                    $order->total_harga,
                    $order->status,
                    $order->payment_status ?? '-',
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
