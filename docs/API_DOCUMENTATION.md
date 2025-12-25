# MancinginAje API Documentation

**Project:** MancinginAje E-Commerce  
**Version:** 1.0.0  
**Base URL:** `http://localhost:8000`

---

## Quick Start

### Import ke Postman
1. Import file `postman/mancinginaje_collection.json`
2. Import environment `postman/Env MancinginAje.postman_environment.json`
3. Set variable `base_url` ke alamat server Anda

### Environment Variables
| Variable | Deskripsi | Contoh |
|----------|-----------|--------|
| `base_url` | Base URL server | `http://localhost:8000` |
| `csrf_token` | Token CSRF untuk web routes | (auto dari cookie) |
| `bearer_token` | Token Sanctum untuk API auth | (dari Tinker) |

---

## Authentication

### Session-Based (Web Routes)
Web routes menggunakan session cookie + CSRF token.

**Flow:**
1. `GET /login` → Dapatkan cookie `XSRF-TOKEN`
2. `POST /login` → Login dengan email/password
3. Sertakan header `X-CSRF-TOKEN` untuk POST/PUT/DELETE

### Bearer Token (API Routes)
API routes menggunakan Laravel Sanctum.

**Generate Token via Tinker:**
```bash
php artisan tinker
$user = App\Models\User::where('email', 'email@example.com')->first();
$token = $user->createToken('api-token')->plainTextToken;
```

**Header:** `Authorization: Bearer {token}`

---

## Endpoints

### 🔐 Authentication

#### Register User
```
POST /register/submit
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Nama lengkap |
| email | string | Yes | Email unik |
| password | string | Yes | Min 8 karakter |
| password_confirmation | string | Yes | Konfirmasi password |
| role | string | Yes | `customer` atau `seller` |
| phone | string | No | Nomor telepon |
| address | string | No | Alamat |

**Response:** Redirect to dashboard/beranda

---

#### Login
```
POST /login
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type | Required |
|-------|------|----------|
| email | string | Yes |
| password | string | Yes |

**Response:** Redirect + Session cookie

---

#### Logout
```
POST /logout
Header: X-CSRF-TOKEN: {csrf_token}
```

**Response:** Redirect to home

---

#### Get Authenticated User (API)
```
GET /api/user
Header: Authorization: Bearer {token}
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "customer"
}
```

---

### 📦 Products

#### Get All Products (API)
```
GET /api/products
```

**Response:**
```json
{
  "status": true,
  "messege": "Data ditemukan",
  "data": [
    {
      "id": 1,
      "name": "Joran Pancing Pro",
      "price": 250000,
      "stock": 50,
      "description": "Joran pancing premium",
      "image": "https://i.imgur.com/xxx.jpg",
      "category_id": 1
    }
  ]
}
```

---

#### Search Suggestions
```
GET /api/search-suggestions?q={query}
```

**Query Params:**
| Param | Description |
|-------|-------------|
| q | Kata kunci pencarian |

**Response:**
```json
{
  "suggestions": ["Joran Pancing", "Joran Shimano"]
}
```

---

#### Product Detail (Web)
```
GET /beranda/detail/{id}
```

**Response:** HTML page

---

### 🛒 Cart

#### View Cart
```
GET /beranda/cart
```

**Response:** HTML page dengan items keranjang

---

#### Add to Cart
```
POST /beranda/cart/add
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

**Body:**
```json
{
  "id": 1,
  "quantity": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Produk berhasil ditambahkan",
  "cart_count": 3
}
```

---

#### Update Cart Item
```
POST /beranda/cart/update/{product_id}
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

**Body:**
```json
{
  "quantity": 3
}
```

---

#### Remove from Cart
```
DELETE /beranda/cart/{product_id}
Header: X-CSRF-TOKEN: {csrf_token}
```

---

#### Save Shipping Option
```
POST /beranda/cart/shipping
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

**Body:**
```json
{
  "cost": 25000,
  "service": "REG",
  "courier": "jne",
  "district_id": 1234
}
```

---

### 🚚 Shipping (RajaOngkir)

#### Get Cities by Province
```
GET /cities/{provinceId}
```

**Response:**
```json
{
  "data": [
    { "id": 1, "name": "Jakarta Pusat" },
    { "id": 2, "name": "Jakarta Selatan" }
  ]
}
```

---

#### Get Districts by City
```
GET /districts/{cityId}
```

**Response:**
```json
{
  "data": [
    { "id": 1, "name": "Menteng" },
    { "id": 2, "name": "Gambir" }
  ]
}
```

---

#### Check Shipping Cost
```
POST /check-ongkir
Content-Type: application/json
```

**Body:**
```json
{
  "district_id": 1234,
  "courier": "jne",
  "weight": 1000
}
```

**Response:**
```json
{
  "results": [
    {
      "service": "REG",
      "cost": 25000,
      "etd": "2-3 hari"
    }
  ]
}
```

---

### 🚢 Biteship Integration

#### Get Shipping Quote
```
POST /biteship/quote
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

**Body:**
```json
{
  "origin": {
    "latitude": -6.2088,
    "longitude": 106.8456
  },
  "destination": {
    "latitude": -6.9175,
    "longitude": 107.6191
  },
  "weight": 1000
}
```

---

#### Create Shipment
```
POST /biteship/create-shipment
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

**Body:**
```json
{
  "order_id": 123,
  "service": "jne_reg"
}
```

---

#### Track Shipment
```
GET /biteship/track/{awb}
```

**Response:**
```json
{
  "status": "delivered",
  "history": [...]
}
```

---

### 💳 Payment (Midtrans)

#### Payment Page
```
GET /beranda/payment
Auth: Required
```

---

#### Create Payment
```
POST /beranda/payment
Auth: Required
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type | Required |
|-------|------|----------|
| payment_method | string | Yes |
| customer_name | string | No |
| customer_email | string | No |
| customer_phone | string | No |

**Response:** Redirect to Snap payment page

---

#### Retry Payment
```
GET /beranda/payment/retry/{order_id}
Auth: Required
```

---

#### Payment Success Page
```
GET /beranda/payment/success
Auth: Required
```

---

#### Snap Callback (Client)
```
POST /beranda/payment/snap-callback
Auth: Required
Content-Type: application/json
```

**Body:**
```json
{
  "order_id": "123-1700000000",
  "transaction_id": "tx-abc-123"
}
```

**Response:**
```json
{
  "success": true,
  "payment_status": "paid"
}
```

---

#### Midtrans Webhook
```
POST /beranda/payment/notification
Content-Type: application/json
No CSRF Required
```

**Body (from Midtrans):**
```json
{
  "transaction_status": "capture",
  "order_id": "123-1700000000",
  "gross_amount": "275000.00",
  "payment_type": "credit_card",
  "signature_key": "..."
}
```

---

#### Poll Order Status
```
GET /beranda/api/order-status/{order_id}
Auth: Required
```

**Response:**
```json
{
  "id": 123,
  "status": "pending",
  "payment_status": "paid",
  "transaction_id": "tx-abc-123"
}
```

---

#### Clear Cart After Payment
```
POST /beranda/api/order-clear/{order_id}
Auth: Required
Content-Type: application/json
```

**Response:**
```json
{
  "cleared": true,
  "payment_status": "paid"
}
```

---

### 📋 Orders

#### View Orders
```
GET /beranda/yourorders
Auth: Required
```

---

#### Cancel Order
```
POST /beranda/yourorders/{order_id}/cancel
Auth: Required
Header: X-CSRF-TOKEN: {csrf_token}
```

---

### 👤 Profile

#### View Profile
```
GET /beranda/profile
Auth: Required
```

---

#### Update Profile
```
POST /beranda/profile/update
Auth: Required
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type | Required |
|-------|------|----------|
| name | string | Yes |
| email | string | Yes |
| phone | string | No |

---

#### Add/Update Address
```
POST /beranda/profile/address
Auth: Required
Header: X-CSRF-TOKEN: {csrf_token}
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type |
|-------|------|
| label | string |
| address_line | string |
| province_id | integer |
| province_name | string |
| city_id | integer |
| city_name | string |
| district_id | integer |
| district_name | string |
| postal_code | string |
| is_default | boolean |

---

#### Delete Address
```
DELETE /beranda/profile/address/{address_id}
Auth: Required
Header: X-CSRF-TOKEN: {csrf_token}
```

---

#### Order History
```
GET /beranda/profile/history
Auth: Required
```

---

### 🏪 Seller Dashboard

> **Auth Required:** Session + Role: `seller`

#### Dashboard
```
GET /beranda/dashboard
```

#### Create Product
```
GET /beranda/dashboard/create
POST /beranda/dashboard
```

**Body (multipart/form-data atau form-urlencoded):**
| Field | Type |
|-------|------|
| name | string |
| price | integer |
| stock | integer |
| description | string |
| image | file/url |
| category_id | integer |

#### Edit Product
```
GET /beranda/dashboard/{id}/edit
PUT /beranda/dashboard/{id}
```

#### Delete Product
```
DELETE /beranda/dashboard/{id}
```

---

### 🔧 Admin Dashboard

> **Auth Required:** Session + Role: `admin`

#### Admin Dashboard
```
GET /admin/dashboard
```

#### Restock Product
```
POST /admin/dashboard/{id}/restock
Content-Type: application/x-www-form-urlencoded
```

**Body:**
| Field | Type |
|-------|------|
| add_stock | integer |

#### Admin Product CRUD
```
GET  /admin/dashboard/create
POST /admin/dashboard
GET  /admin/dashboard/{id}/edit
PUT  /admin/dashboard/{id}
DELETE /admin/dashboard/{id}
```

#### Reports
```
GET /admin/reports
```

#### Manage Orders
```
GET /admin/orders
POST /admin/orders/{id}/cancel
POST /admin/orders/{id}/accept
```

#### Delete Seller
```
DELETE /admin/sellers/{id}
```

#### Delete User
```
DELETE /admin/users/{id}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized."
}
```

### Not Found (404)
```json
{
  "message": "Not found."
}
```

---

## Testing dengan Postman

### Login Flow
1. `GET {{base_url}}/` → Ekstrak `XSRF-TOKEN` dari cookie
2. `POST {{base_url}}/login` dengan body email & password
3. Postman akan menyimpan session cookie otomatis
4. Set header `X-CSRF-TOKEN` dari cookie untuk requests selanjutnya

### Checkout Flow
1. Login sebagai customer
2. `POST {{base_url}}/beranda/cart/add` → Tambah produk
3. `POST {{base_url}}/check-ongkir` → Cek ongkir
4. `POST {{base_url}}/beranda/cart/shipping` → Simpan shipping
5. `POST {{base_url}}/beranda/payment` → Buat order
6. Complete payment via Snap
7. `GET {{base_url}}/beranda/api/order-status/:id` → Poll status

---

## Notes

- Semua web routes memerlukan CSRF token untuk POST/PUT/DELETE
- Admin dan Seller routes dilindungi middleware role
- Midtrans webhook tidak memerlukan CSRF (sudah di-exclude)
- Untuk testing Midtrans, gunakan sandbox keys di `.env`
