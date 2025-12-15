# MancinginAje - Platform E-Commerce Alat Pancing

MancinginAje adalah platform e-commerce terpadu untuk penjualan alat pancing online dengan integrasi payment gateway Midtrans. Aplikasi ini dibangun menggunakan Laravel dan dilengkapi dengan fitur lengkap untuk pembelian dan pembayaran.

## 🎯 Fitur Utama

### 1. **Autentikasi & User Management**
- Registrasi pengguna dengan pilihan role (Customer/Seller)
- Login/Logout yang aman
- Data profil lengkap (nama, email, telepon, alamat)

### 2. **Katalog Produk**
- 6 kategori produk: Joran, Reel, Kail, Umpan, Pakaian, Aksesoris
- Sistem pencarian produk
- Filter berdasarkan kategori
- Tampilan detail produk lengkap

### 3. **Keranjang Belanja**
- Tambah/hapus produk ke keranjang
- Update jumlah item
- Perhitungan otomatis total belanja
- Sistem session-based untuk persistence

### 4. **Sistem Pembayaran (Midtrans Integration)**
- Form pengisian data pengiriman
- Pilihan metode pembayaran:
  - Transfer Bank
  - E-Wallet (GCash, OVO, DANA, LinkAja)
  - Kartu Kredit/Debit
- Snap Embed untuk tampilan payment gateway
- Notifikasi callback dari Midtrans

### 5. **Order Management**
- Halaman pesanan saya dengan status pembayaran
- Detail pesanan lengkap
- Tracking pembayaran
- Riwayat pembelian

### 6. **Dashboard Penjual** (Optional)
- Kelola produk
- Lihat orders
- Update status orders

## 📦 Stack Teknologi

- **Backend**: Laravel 12.0
- **Database**: PostgreSQL (Supabase)
- **Frontend**: Bootstrap 5, Blade Template
- **Payment Gateway**: Midtrans (Snap)
- **Session Management**: Database-based
- **Authentication**: Laravel Built-in Auth

## 🚀 Instalasi & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm (optional, untuk frontend tooling)
- PostgreSQL Database
- Midtrans Account (untuk payment)

### Langkah-langkah Instalasi

1. **Clone Repository**
```bash
cd c:\Projek\WEB\12-E_Commerce_MancingJoki\MancinginAje
```

2. **Install Dependencies**
```bash
composer install
```

3. **Setup Environment**
```bash
cp .env.example .env
```

4. **Generate App Key**
```bash
php artisan key:generate
```

5. **Konfigurasi Database di .env**
```
DB_CONNECTION=pgsql
DB_URL=postgresql://user:password@host:port/database
```

6. **Konfigurasi Midtrans di .env**
```
MIDTRANS_MERCHANT_ID=your_merchant_id
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_IS_PRODUCTION=false
```

7. **Jalankan Migration**
```bash
php artisan migrate
```

8. **Jalankan Seeder (Optional - untuk data sampel)**
```bash
php artisan db:seed
```

9. **Jalankan Development Server**
```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 📋 Database Schema

### Tables Utama

#### `users`
- id, name, email, password, role (customer/seller)
- address, phone
- timestamps

#### `categories`
- id, nama
- timestamps

#### `products`
- id, nama, deskripsi, harga, stok, gambar
- category_id (foreign key)
- timestamps

#### `orders`
- id, user_id, total_harga, status
- customer_name, customer_email, customer_phone
- shipping_address, payment_method
- transaction_id, payment_status
- timestamps

#### `order_items`
- id, order_id, product_id
- quantity, price
- timestamps

## 🔐 Alur Pembayaran

1. **User Login** - Masuk dengan akun terdaftar
2. **Browse Produk** - Lihat katalog dan pilih produk
3. **Tambah ke Keranjang** - Masukkan produk ke keranjang
4. **Checkout** - Klik lanjut pembayaran
5. **Isi Data Pengiriman** - Lengkapi form biodata
6. **Pilih Metode Pembayaran** - Pilih salah satu cara bayar
7. **Redirect ke Midtrans** - Lakukan pembayaran
8. **Konfirmasi Pembayaran** - Halaman sukses pembayaran
9. **Lihat Pesanan** - Check status di menu Pesanan Saya

## 📝 Kategori Produk

1. **Joran** - Pancing berbagai ukuran dan material
2. **Reel** - Mesin pancing manual dan elektrik
3. **Kail** - Berbagai ukuran kail pancing
4. **Umpan** - Umpan buatan dan pelet
5. **Pakaian** - Jaket, rompi, dan perlengkapan
6. **Aksesoris** - Tas, lampu, dan aksesoris lainnya

## 🛣️ Routes Utama

### Public Routes
- `GET /` - Halaman beranda
- `GET /register` - Registrasi user
- `POST /register/submit` - Submit registrasi
- `GET /login` - Login page
- `POST /login` - Submit login
- `GET /beranda/orders` - Katalog produk
- `GET /beranda/detail/{id}` - Detail produk

### Protected Routes (Require Auth)
- `POST /logout` - Logout
- `GET /beranda/cart` - Keranjang belanja
- `POST /beranda/cart/add` - Tambah ke keranjang
- `POST /beranda/cart/update/{id}` - Update keranjang
- `DELETE /beranda/cart/{id}` - Hapus dari keranjang
- `GET /beranda/payment` - Halaman pembayaran
- `POST /beranda/payment` - Proses pembayaran
- `GET /beranda/payment/success` - Halaman sukses
- `GET /beranda/yourorders` - Pesanan saya

### Callback Routes
- `POST /beranda/payment/notification` - Midtrans Callback

## 🎨 UI/UX Features

- **Responsive Design** - Mobile-friendly interface
- **Modern Styling** - Gradient navbar, smooth transitions
- **Interactive Elements** - Hover effects, smooth animations
- **Clear CTAs** - Tombol action yang jelas
- **Form Validation** - Validasi input di client & server

## ⚙️ Konfigurasi Penting

### Midtrans Configuration (`config/midtrans.php`)
```php
'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
'client_key' => env('MIDTRANS_CLIENT_KEY'),
'server_key' => env('MIDTRANS_SERVER_KEY'),
'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
```

### Session Configuration
- Driver: database
- Lifetime: 120 menit
- Encrypt: false (sesuaikan dengan kebutuhan)

## 📊 Status Pembayaran

| Status | Keterangan |
|--------|------------|
| pending | Menunggu pembayaran |
| paid | Pembayaran berhasil |
| expired | Waktu pembayaran berakhir |
| failed | Pembayaran gagal |

## 🔗 Relasi Model

- User has Many Orders
- Order belongs to User
- Order has Many OrderItems
- OrderItem belongs to Order & Product
- Product belongs to Category
- Category has Many Products

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan PostgreSQL server running
- Check DB credentials di .env

### Midtrans Integration Error
- Verify credentials di .env
- Check if using sandbox/production mode correctly
- Test dengan Midtrans SDK

### Session Issues
- Clear session: `php artisan session:table`
- Run migration: `php artisan migrate`

## 📞 Support & Contact

- **Email**: info@mancinginaje.com
- **Phone**: +62 812-3456-7890
- **Address**: Jalan Mancingaji No. 123, Kota Anda

## 📄 License

MIT License - Free to use and modify

## ✨ Masa Depan

- [ ] Admin Dashboard
- [ ] Order tracking real-time
- [ ] Review & Rating system
- [ ] Wishlist feature
- [ ] Multi-payment gateway support
- [ ] Push notification
- [ ] Mobile App
- [ ] Email notification

---

**Developed by**: Admin MancinginAje
**Last Updated**: December 2025
