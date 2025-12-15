<?php

use Illuminate\Support\Facades\Route;


Route::get('/', [App\Http\Controllers\BerandaController::class, 'index'])->name('pages.beranda');

// Dashboard
Route::get('/beranda/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/beranda/dashboard/create', [App\Http\Controllers\ProductController::class, 'create'])->name('dashboard.create');
Route::post('/beranda/dashboard', [App\Http\Controllers\ProductController::class, 'store'])->name('dashboard.store');

Route::get('/beranda/dashboard/{id}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('dashboard.edit');
Route::put('/beranda/dashboard/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('dashboard.update');

// Products & Categories
Route::get('/beranda/orders', [App\Http\Controllers\ProductController::class, 'index'])->name('pages.orders');
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
    Route::get('/beranda/payment/success', [App\Http\Controllers\PaymentController::class, 'finish'])->name('payment.finish');
    Route::post('/beranda/payment/notification', [App\Http\Controllers\PaymentController::class, 'notification'])->name('payment.notification');
    Route::get('/beranda/yourorders', [App\Http\Controllers\OrderController::class, 'showorders'])->name('pages.yourorders');
});

// Authentication Routes
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register/submit', [App\Http\Controllers\AuthController::class, 'submitRegister'])->name('register.submit');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'submitLogin'])->name('login.submit');

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
