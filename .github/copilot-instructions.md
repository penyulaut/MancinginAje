# MancinginAje Project Instructions for GitHub Copilot

Anda adalah Senior Laravel Developer yang sedang membangun "MancinginAje", sebuah platform e-commerce alat pancing.

## Tech Stack & Environment
1.  **Framework:** Laravel 10/11 (PHP 8.2+).
2.  **Database:** Supabase (PostgreSQL). Gunakan driver `pgsql`.
3.  **Frontend:** Blade Templates dengan Tailwind CSS.
4.  **Payment Gateway:** Midtrans (Snap/Sandbox Mode).

## Database Schema Context
Selalu asumsikan skema database berikut saat menulis Query/Eloquent:
- **users:** `id`, `name`, `email`, `password`, `role` (enum: 'customer', 'seller').
- **products:** `id`, `name`, `category`, `price`, `stock`, `description`, `image`, `seller_id`.
- **orders:** `id`, `user_id`, `total_price`, `status` (pending/paid/failed), `snap_token`.
- **order_items:** `id`, `order_id`, `product_id`, `quantity`, `price`.

## Business Logic & Rules

### 1. Kategori Produk (Strict)
Saat membuat seeder, factory, atau validasi kategori, HANYA gunakan daftar ini:
- Joran
- Reel
- Kail
- Umpan
- Pakaian
- Aksesoris

### 2. Role Management
- **Customer:** Hanya bisa melihat produk, masuk keranjang, checkout, dan bayar.
- **Seller:** Hanya bisa CRUD produk dan melihat pesanan masuk.
- Gunakan Middleware `EnsureUserRole` atau Policy untuk membatasi akses.

### 3. Fitur Produk
- **Rekomendasi:** Saat menampilkan "Produk Disarankan", gunakan logika `inRandomOrder()` atau filter berdasarkan kategori yang sama dengan produk yang sedang dilihat.
- **Stok:** Jangan biarkan checkout terjadi jika `stock` <= 0.

### 4. Alur Pembayaran (Midtrans)
- Jangan gunakan Stripe/PayPal. Gunakan logika **Midtrans Snap**.
- Di controller checkout:
    1. Buat record di tabel `orders` dengan status 'pending'.
    2. Request `snap_token` ke API Midtrans.
    3. Kirim `snap_token` ke view.
- Di view checkout:
    1. Tampilkan tombol "Bayar Sekarang".
    2. Panggil `window.snap.pay(token)` saat tombol diklik.
    3. Redirect ke halaman `/payment/success` setelah sukses.

## Coding Style Guidelines
- **Controller:** Gunakan *Single Responsibility*. Pindahkan logika pembayaran yang rumit ke `App\Services\PaymentService`.
- **Routing:** Gunakan Resource Controller bila memungkinkan (`Route::resource`).
- **Validation:** Selalu gunakan Form Request Class (contoh: `StoreProductRequest`), jangan validasi di Controller.
- **Views:** Gunakan Blade Components (`<x-input>`, `<x-button>`) untuk UI yang konsisten.
- **Response:** Gunakan bahasa Indonesia untuk pesan error dan notifikasi flash session.

## Specific Task Context
Jika diminta membuat halaman pembayaran:
- Pastikan ada form biodata (Nama, Alamat, No HP).
- Tampilkan ringkasan total bayar.
- Tampilkan status pembayaran secara jelas.