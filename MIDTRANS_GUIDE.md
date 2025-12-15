# 📱 Panduan Integrasi Midtrans

## 🎯 Tentang Midtrans

Midtrans adalah payment gateway terpercaya yang mendukung berbagai metode pembayaran termasuk:
- Transfer Bank (e-Banking)
- E-Wallet (GCash, OVO, DANA, LinkAja, dll)
- Kartu Kredit/Debit
- BNPL (Buy Now Pay Later)

## 🔑 Mendapatkan Credentials

### 1. Buat Akun Midtrans
- Kunjungi: https://dashboard.midtrans.com/register
- Daftar dengan email bisnis Anda
- Verifikasi email

### 2. Login ke Dashboard
- Masuk ke: https://dashboard.midtrans.com
- Navigasi ke Settings > Access Keys

### 3. Copy Credentials
Anda akan menemukan:
- **Merchant ID**: Identifier unik untuk toko Anda
- **Client Key**: Public key untuk Snap frontend
- **Server Key**: Private key untuk backend (JANGAN SHARE!)

## 🔧 Konfigurasi di Project

### 1. Update .env File
```env
MIDTRANS_MERCHANT_ID=M123456
MIDTRANS_CLIENT_KEY=VT-cbDa6sJDjMfJ-U-8s
MIDTRANS_SERVER_KEY=SB-Mid-server-Xxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

**Catatan**: 
- Gunakan `false` untuk development/sandbox
- Gunakan `true` untuk production
- Server Key dimulai dengan `SB-` untuk sandbox, `Mid-` untuk production

### 2. Config File (config/midtrans.php)
Sudah dibuat dan siap digunakan:
```php
return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
];
```

## 🚀 Payment Flow

### 1. **Customer Checkout**
```
User klik "Lanjutkan Pembayaran" → Masuk Payment Page
```

### 2. **Backend Create Order**
```php
// Dalam PaymentController@store
$order = Orders::create([
    'user_id' => auth()->id(),
    'customer_name' => $validated['customer_name'],
    'customer_email' => $validated['customer_email'],
    'customer_phone' => $validated['customer_phone'],
    'shipping_address' => $validated['shipping_address'],
    'payment_method' => $validated['payment_method'],
    'total_harga' => $total,
    'status' => 'pending',
    'payment_status' => 'pending',
]);
```

### 3. **Generate Snap Token**
```php
// Dari MidtransService@createSnapToken
$snapToken = Snap::getSnapToken($payload);
```

Payload berisi:
- Transaction details (order ID, amount)
- Customer details (name, email, phone)
- Item details (product info)

### 4. **Display Payment Gateway**
```blade
<!-- Dalam payment-process.blade.php -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script type="text/javascript">
    snap.embed('{{ $snapToken }}', {
        embedId: 'snap-container'
    });
</script>
```

### 5. **Customer Payment**
- Customer memilih metode pembayaran
- Lakukan pembayaran sesuai instruksi
- Midtrans process pembayaran
- Redirect ke success page

### 6. **Notification & Callback**
Midtrans mengirim POST request ke:
```
POST /beranda/payment/notification
```

Data yang diterima:
```json
{
    "transaction_id": "6cc831eca1xxxxxxx",
    "order_id": "12-1234567890",
    "transaction_status": "settlement",
    "fraud_status": "accept"
}
```

### 7. **Update Order Status**
```php
// Dalam MidtransService@handleNotification
if ($transactionStatus == 'settlement') {
    $order->payment_status = 'paid';
    $order->status = 'paid';
}
```

## 📊 Transaction Status Mapping

| Midtrans Status | App Status | Keterangan |
|-----------------|------------|-----------|
| settlement | paid | Pembayaran berhasil |
| capture | pending/paid | Pending review fraud |
| pending | pending | Menunggu pembayaran |
| expire | expired | Waktu pembayaran habis |
| cancel | cancelled | Transaksi dibatalkan |
| deny | failed | Pembayaran ditolak |

## 🔒 Security Best Practices

### 1. **Server Key Protection**
❌ JANGAN pernah expose Server Key ke frontend
✅ Simpan di .env dan akses hanya di backend

### 2. **Verify Signature**
```php
// Verifikasi notification dari Midtrans
$notif = json_decode($request->getContent());
// Validate dengan signature
```

### 3. **HTTPS Only**
- Gunakan HTTPS di production
- Midtrans hanya mengirim notifikasi via HTTPS

### 4. **Whitelist IP**
Di Midtrans Dashboard Settings:
- Whitelist IP server untuk API calls
- Whitelist notification sender IPs

## 🧪 Testing Payment Flow

### 1. **Sandbox Environment**
- Gunakan test credentials dari dashboard
- Transaction status akan instant atau pending

### 2. **Test Cards**
Midtrans menyediakan test cards:

**Success Payment:**
- Card Number: `4811 1111 1111 1114`
- Expiry: `12/25`
- CVV: `123`

**Payment Failure:**
- Card Number: `4111 1111 1111 1113`
- Expiry: `12/25`
- CVV: `123`

### 3. **Test E-Wallet**
- GCash: Select during payment
- OVO: Select during payment
- DANA: Select during payment

## 📈 Monitoring & Debugging

### 1. **Check Dashboard**
- Login ke: https://dashboard.midtrans.com
- Transactions → Lihat detail transaksi
- Check payment status

### 2. **API Reference**
- Docs: https://api-docs.midtrans.com
- Check request/response format

### 3. **Log Files**
Di Laravel:
```
storage/logs/laravel.log
```

## 🚨 Common Issues & Solutions

### Issue 1: "Failed to get Snap Token"
**Cause**: Invalid Server Key atau Merchant ID
**Solution**: 
- Verify credentials di .env
- Check sandbox vs production settings

### Issue 2: "Invalid Client Key"
**Cause**: Client Key tidak match dengan Merchant ID
**Solution**:
- Copy dari same Merchant di Dashboard
- Restart server

### Issue 3: Notification tidak masuk
**Cause**: HTTPS not enabled atau IP not whitelisted
**Solution**:
- Enable HTTPS
- Whitelist Midtrans IPs di firewall
- Check notification URL di Dashboard

### Issue 4: Payment Status stuck di "Pending"
**Cause**: Notification belum di-process
**Solution**:
- Check callback URL di Dashboard Settings
- Verify URL accessibility
- Check firewall/proxy settings

## 🔄 Payment Status Workflow

```
Order Created (pending)
        ↓
Customer proceed to payment
        ↓
Midtrans Snap opens
        ↓
Customer selects payment method
        ↓
Payment Processing
        ↓
Midtrans sends notification
        ↓
Order status updated (paid/failed)
        ↓
Customer sees confirmation
        ↓
Order ready for fulfillment
```

## 📧 Email Notifications

Sistem akan mengirim email ke customer:
- Confirmation pembayaran (manual atau auto)
- Receipt order
- Shipping notification (manual)

## 💳 Supported Payment Methods

### Bank Transfer
- BCA, BNI, Mandiri, CIMB, Permata, etc.

### E-Wallet
- GCash, OVO, DANA, LinkAja, Gopay, dll

### Credit/Debit Card
- Visa, Mastercard, JCB

### BNPL
- Akulaku, Kredivo, dll

## 🎓 Resources

- **Official Docs**: https://docs.midtrans.com
- **API Documentation**: https://api-docs.midtrans.com
- **Dashboard**: https://dashboard.midtrans.com
- **Support**: https://midtrans.com/contact

## 📞 Support

Jika mengalami masalah:
1. Check Midtrans documentation
2. Visit Dashboard → Help center
3. Contact Midtrans support

---

**Last Updated**: December 2025
**Midtrans Integration**: v1.0
