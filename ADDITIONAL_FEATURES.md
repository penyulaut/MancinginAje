# 🎯 Fitur Tambahan & Pengembangan Lanjutan

Panduan untuk menambahkan fitur-fitur lanjutan ke MancinginAje e-commerce.

## 1️⃣ Admin Dashboard

### Fitur yang Diperlukan:
- Dashboard dengan statistik (total orders, revenue, customers)
- Manage products (CRUD)
- Manage orders & status
- Manage users
- Reports & analytics

### Cara Implementasi:
```php
// Create AdminController
php artisan make:controller AdminController

// Create admin middleware
php artisan make:middleware AdminMiddleware

// Add admin routes di web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('products', AdminProductController::class);
    Route::resource('orders', AdminOrderController::class);
    Route::resource('users', AdminUserController::class);
});
```

## 2️⃣ Email Notifications

### Fitur yang Diperlukan:
- Order confirmation email
- Payment success email
- Shipping notification
- Invoice generation

### Cara Implementasi:
```php
// Create mailable
php artisan make:mailable OrderConfirmation

// Setup di .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

// Send email di controller
Mail::to($order->customer_email)
    ->send(new OrderConfirmation($order));
```

## 3️⃣ Review & Rating System

### Database Schema:
```php
// Create migration
php artisan make:migration create_reviews_table

Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->integer('rating'); // 1-5
    $table->text('comment');
    $table->timestamps();
});
```

### Controller:
```php
php artisan make:controller ReviewController

// Add routes
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth');
```

## 4️⃣ Wishlist / Favorites

### Database:
```php
php artisan make:migration create_wishlists_table

Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('product_id')->constrained();
    $table->timestamps();
    $table->unique(['user_id', 'product_id']);
});
```

### Usage:
```php
// Add to wishlist
$user->wishlists()->attach($productId);

// Remove from wishlist
$user->wishlists()->detach($productId);

// Get wishlists
$wishlists = auth()->user()->wishlists;
```

## 5️⃣ Order Tracking

### Fitur:
- Real-time order status updates
- Shipping tracker integration
- SMS/Email notifications

### Status Workflow:
```
pending → paid → processing → shipped → delivered
```

### Implementation:
```php
// Add status column to orders
$table->enum('status', [
    'pending', 'paid', 'processing', 'shipped', 'delivered'
]);

// Create OrderStatusUpdated event
php artisan make:event OrderStatusUpdated

// Notify customer
event(new OrderStatusUpdated($order));
```

## 6️⃣ Search & Filter Enhancement

### Advanced Filtering:
- Filter by price range
- Filter by rating
- Filter by in-stock only
- Sort by popularity, price, newest

### Implementation:
```php
// Update ProductController@index
$products = Product::query()
    ->when($request->min_price, function ($q) use ($request) {
        return $q->where('harga', '>=', $request->min_price);
    })
    ->when($request->max_price, function ($q) use ($request) {
        return $q->where('harga', '<=', $request->max_price);
    })
    ->when($request->sort, function ($q) use ($request) {
        return match($request->sort) {
            'popular' => $q->orderBy('views', 'desc'),
            'price_low' => $q->orderBy('harga', 'asc'),
            'price_high' => $q->orderBy('harga', 'desc'),
            'newest' => $q->latest(),
            default => $q,
        };
    })
    ->get();
```

## 7️⃣ Discount & Coupon System

### Database:
```php
php artisan make:migration create_coupons_table

Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->integer('discount'); // percentage or amount
    $table->enum('type', ['percentage', 'fixed']);
    $table->date('valid_until');
    $table->integer('max_uses')->nullable();
    $table->integer('used_count')->default(0);
    $table->timestamps();
});
```

### Validation:
```php
public function applyCoupon(Request $request)
{
    $coupon = Coupon::where('code', $request->coupon_code)
        ->where('valid_until', '>=', today())
        ->where(function($q) {
            return $q->whereNull('max_uses')
                ->orWhereRaw('used_count < max_uses');
        })->first();
    
    if (!$coupon) return back()->with('error', 'Invalid coupon');
    
    session()->put('coupon', $coupon);
    return back()->with('success', 'Coupon applied!');
}
```

## 8️⃣ API Integration (RESTful)

### Create API Routes:
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::get('/products', [ApiProductController::class, 'index']);
    Route::get('/products/{id}', [ApiProductController::class, 'show']);
    Route::get('/categories', [ApiCategoryController::class, 'index']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/orders', [ApiOrderController::class, 'store']);
        Route::get('/orders', [ApiOrderController::class, 'index']);
    });
});
```

### Response Format:
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [],
    "pagination": {
        "current_page": 1,
        "total": 100,
        "per_page": 10
    }
}
```

## 9️⃣ Push Notifications

### Using Laravel Notifications:
```php
php artisan make:notification OrderStatusUpdated

// Implement channels: mail, SMS, web push
->via(['mail', 'database', 'nexmo'])
```

### Web Push:
```php
// Install package
composer require laravel/echo

// Setup WebSocket server
npm install --save-dev laravel-echo pusher-js
```

## 🔟 Mobile App Integration

### Steps:
1. Create API endpoints (RESTful)
2. Add authentication (Sanctum tokens)
3. Build React Native / Flutter app
4. Implement push notifications
5. Offline capability

### Installation Sanctum:
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## 1️⃣1️⃣ Analytics & Reporting

### Track Data:
- Product views
- Sales trends
- Customer behavior
- Revenue analysis

### Implementation:
```php
// Create tracking table
Schema::create('product_views', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('user_id')->nullable();
    $table->string('ip_address');
    $table->timestamps();
});

// Track views
ProductView::create([
    'product_id' => $product->id,
    'user_id' => auth()->id(),
    'ip_address' => request()->ip(),
]);
```

## 1️⃣2️⃣ Inventory Management

### Features:
- Stock alerts when low
- Automatic reorder notifications
- Warehouse management

### Implementation:
```php
// Add columns
$table->integer('min_stock')->default(5);
$table->integer('reorder_quantity')->default(50);

// Check stock
if ($product->stok < $product->min_stock) {
    // Send alert to admin
    Notification::send($admins, new LowStockAlert($product));
}
```

## 🛠️ Development Tips

### Using Laravel Tinker:
```bash
php artisan tinker

>>> $user = User::first();
>>> $user->orders()->count();
>>> $order = Order::find(1);
>>> $order->items;
```

### Database Optimization:
```php
// Use eager loading
$orders = Order::with('items.product', 'user')->get();

// Add indexes
$table->index('user_id');
$table->index('created_at');
```

### Caching:
```php
// Cache products
$products = cache()->remember('products', 3600, function () {
    return Product::all();
});

// Clear cache
cache()->forget('products');
```

## 📚 Useful Packages

```bash
# For image optimization
composer require intervention/image

# For CSV export
composer require maatwebsite/excel

# For PDF generation
composer require barryvdh/laravel-dompdf

# For admin panel (optional)
composer require filament/filament

# For API documentation
composer require scramble/scramble
```

## 🔐 Performance Optimization

### 1. Database Queries
```php
// Bad
foreach ($orders as $order) {
    echo $order->user->name; // N+1 query problem
}

// Good
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name;
}
```

### 2. Cache Implementation
```php
// Cache homepage data
Route::get('/', function () {
    $data = cache()->remember('homepage_data', 86400, function () {
        return [
            'categories' => Category::all(),
            'featured_products' => Product::featured()->get(),
        ];
    });
    
    return view('pages.beranda', $data);
});
```

### 3. Asset Optimization
```blade
<!-- Minify CSS & JS -->
<link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
<script src="{{ asset('js/app.min.js') }}" defer></script>

<!-- Lazy load images -->
<img src="product.jpg" loading="lazy" alt="Product">
```

## 🚀 Deployment Checklist

- [ ] Enable query caching
- [ ] Configure storage for production
- [ ] Setup email service
- [ ] Enable HTTPS
- [ ] Configure CDN for assets
- [ ] Setup error tracking (Sentry)
- [ ] Enable rate limiting
- [ ] Configure CORS properly
- [ ] Backup strategy
- [ ] Monitoring & alerts

## 📖 Learning Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Midtrans Documentation](https://docs.midtrans.com)
- [Bootstrap Documentation](https://getbootstrap.com/docs)

---

**Catatan**: Fitur-fitur di atas dapat ditambahkan secara bertahap sesuai kebutuhan bisnis.

**Last Updated**: December 2025
