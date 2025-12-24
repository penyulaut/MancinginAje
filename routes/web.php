<?php

use Illuminate\Support\Facades\Route;


Route::get('/', [App\Http\Controllers\BerandaController::class, 'index'])->name('pages.beranda');


// Dashboard (protected, seller-only)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsSeller::class])->group(function() {
    Route::get('/beranda/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/beranda/dashboard/create', [App\Http\Controllers\ProductController::class, 'create'])->name('dashboard.create');
    Route::post('/beranda/dashboard', [App\Http\Controllers\ProductController::class, 'store'])->name('dashboard.store');

    Route::get('/beranda/dashboard/{id}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('dashboard.edit');
    Route::put('/beranda/dashboard/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('dashboard.update');
    Route::delete('/beranda/dashboard/{id}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('dashboard.destroy');
});

// Admin dashboard (admin-only) - restock & add products via Imgur link
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function() {
    Route::get('/admin/dashboard', [App\Http\Controllers\DashboardController::class, 'indexAdmin'])->name('admin.dashboard.index');
    Route::post('/admin/dashboard/{id}/restock', [App\Http\Controllers\DashboardController::class, 'restock'])->name('admin.dashboard.restock');

    // Admin may also add products (uses same ProductController but accessible to admin)
    Route::get('/admin/dashboard/create', [App\Http\Controllers\ProductController::class, 'create'])->name('admin.dashboard.create');
    Route::post('/admin/dashboard', [App\Http\Controllers\ProductController::class, 'store'])->name('admin.dashboard.store');
    Route::get('/admin/dashboard/{id}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('admin.dashboard.edit');
    Route::put('/admin/dashboard/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('admin.dashboard.update');
    Route::delete('/admin/dashboard/{id}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('admin.dashboard.destroy');
    // Admin reports
    Route::get('/admin/reports', [App\Http\Controllers\DashboardController::class, 'reports'])->name('admin.reports.index');
    // Admin manage all orders
    Route::get('/admin/orders', [App\Http\Controllers\DashboardController::class, 'orders'])->name('admin.orders.index');
    Route::post('/admin/orders/{id}/cancel', [App\Http\Controllers\DashboardController::class, 'cancelOrder'])->name('admin.orders.cancel');
    Route::post('/admin/orders/{id}/accept', [App\Http\Controllers\DashboardController::class, 'acceptOrder'])->name('admin.orders.accept');
    // Admin manage sellers (delete)
    Route::delete('/admin/sellers/{id}', [App\Http\Controllers\DashboardController::class, 'destroySeller'])->name('admin.sellers.destroy');
    // Admin delete any user (except self)
    Route::delete('/admin/users/{id}', [App\Http\Controllers\DashboardController::class, 'destroyUser'])->name('admin.users.destroy');
});

// Products & Categories
Route::get('/beranda/orders', [App\Http\Controllers\ProductController::class, 'index'])->name('pages.orders');
Route::get('/api/search-suggestions', [App\Http\Controllers\ProductController::class, 'searchSuggestions'])->name('api.search.suggestions');
use App\Http\Controllers\BiteshipController;
Route::get('/beranda/detail/{id}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

// Cart Routes
Route::get('/beranda/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/beranda/cart/add', [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
Route::post('/beranda/cart/update/{id}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/beranda/cart/{id}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');

// Save selected shipping option to session
Route::post('/beranda/cart/shipping', [App\Http\Controllers\CartController::class, 'saveShipping'])->name('cart.shipping');
Route::get('/cities/{provinceId}', [App\Http\Controllers\CartController::class, 'getCities']);
Route::get('/districts/{cityId}', [App\Http\Controllers\CartController::class, 'getDistricts']);
Route::post('/check-ongkir', [App\Http\Controllers\CartController::class, 'checkOngkir']);


// Payment Routes
Route::middleware('auth')->group(function () {
    Route::get('/beranda/payment', [App\Http\Controllers\PaymentController::class, 'index'])->name('payment.index');
    Route::post('/beranda/payment', [App\Http\Controllers\PaymentController::class, 'store'])->name('payment.store');
    Route::get('/beranda/payment/retry/{id}', [App\Http\Controllers\PaymentController::class, 'retry'])->name('payment.retry');


// Biteship integration endpoints
Route::post('/biteship/quote', [BiteshipController::class, 'quote']);
Route::post('/biteship/create-shipment', [BiteshipController::class, 'createShipment']);
Route::get('/biteship/track/{awb}', [BiteshipController::class, 'track']);
    Route::get('/beranda/payment/success', [App\Http\Controllers\PaymentController::class, 'finish'])->name('payment.finish');
    // API for polling order status (used by client when waiting for webhook)
    Route::get('/beranda/api/order-status/{id}', [App\Http\Controllers\PaymentController::class, 'orderStatus'])->name('payment.status');
    Route::post('/beranda/api/order-clear/{id}', [App\Http\Controllers\PaymentController::class, 'clearCartIfPaid'])->name('payment.clear');
    Route::get('/beranda/yourorders', [App\Http\Controllers\OrderController::class, 'showorders'])->name('pages.yourorders');
    Route::post('/beranda/yourorders/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    
    // Profile routes
    Route::get('/beranda/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/beranda/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/beranda/profile/address', [App\Http\Controllers\ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/beranda/profile/address/{id}', [App\Http\Controllers\ProfileController::class, 'deleteAddress'])->name('profile.address.delete');
    Route::get('/beranda/profile/history', [App\Http\Controllers\ProfileController::class, 'history'])->name('profile.history');
});

// Midtrans webhook (public) - called by Midtrans to notify transaction updates
Route::post('/beranda/payment/notification', [App\Http\Controllers\MidtransWebhookController::class, 'handle'])
    ->name('payment.notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Authentication Routes
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register/submit', [App\Http\Controllers\AuthController::class, 'submitRegister'])->name('register.submit');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'submitLogin'])->name('login.submit');

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Test route for CSRF error (remove in production)
Route::get('/test-csrf', function() {
    return view('test-csrf');
})->name('test.csrf');

// Temporary debug route: show Midtrans config (trimmed) — remove after debugging
Route::get('/_debug/midtrans-config', function() {
    $cfg = config('midtrans');
    // mask server key partially for safety
    $cfg['server_key_masked'] = $cfg['server_key'] ? substr($cfg['server_key'], 0, 6) . '...' : null;
    return response()->json([
        'config' => $cfg,
        'env_values' => [
            'MIDTRANS_CLIENT_KEY' => env('MIDTRANS_CLIENT_KEY'),
            'MIDTRANS_SERVER_KEY' => env('MIDTRANS_SERVER_KEY'),
            'MIDTRANS_IS_PRODUCTION' => env('MIDTRANS_IS_PRODUCTION'),
        ]
    ]);
});



// Route::get('/cities/{provinceId}', [App\Http\Controllers\RajaOngkirController::class, 'getCities']);
// //route to get districts based on city ID
// Route::get('/districts/{cityId}', [App\Http\Controllers\RajaOngkirController::class, 'getDistricts']);
// //route to post shipping cost
// Route::post('/check-ongkir', [App\Http\Controllers\RajaOngkirController::class, 'checkOngkir']);
