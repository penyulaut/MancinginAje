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
    Route::get('/beranda/yourorders', [App\Http\Controllers\OrderController::class, 'showorders'])->name('pages.yourorders');
    Route::post('/beranda/yourorders/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    
    // Profile routes
    Route::get('/beranda/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/beranda/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/beranda/profile/address', [App\Http\Controllers\ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/beranda/profile/history', [App\Http\Controllers\ProfileController::class, 'history'])->name('profile.history');
});

// Midtrans webhook (public) - called by Midtrans to notify transaction updates
Route::post('/beranda/payment/notification', [App\Http\Controllers\MidtransWebhookController::class, 'handle'])
    ->name('payment.notification')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Authentication Routes
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register/submit', [App\Http\Controllers\AuthController::class, 'submitRegister'])->name('register.submit');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login.show');
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
