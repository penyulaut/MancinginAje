@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">Pembayaran</h1>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            

            <form action="{{ route('payment.store') }}" method="POST">
                @csrf

                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                   id="customer_name" name="customer_name"
                                   value="{{ old('customer_name', auth()->user()->name ?? '') }}" required>
                            @error('customer_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                                   id="customer_email" name="customer_email"
                                   value="{{ old('customer_email', auth()->user()->email ?? '') }}" required>
                            @error('customer_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                                   id="customer_phone" name="customer_phone"
                                   value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required>
                            @error('customer_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Alamat Pengiriman</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="use_profile_address">
                                <label class="form-check-label small" for="use_profile_address">Gunakan alamat dari profil</label>
                            </div>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                      id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address', auth()->user()->address ?? '') }}</textarea>
                            @error('shipping_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Pilih metode pembayaran. Kami mendukung banyak channel Midtrans.</p>

                        <h6 class="mt-3">Transfer Bank</h6>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bca" value="bank_transfer_bca" {{ old('payment_method')=='bank_transfer_bca' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="bca">BCA (VA)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bni" value="bank_transfer_bni" {{ old('payment_method')=='bank_transfer_bni' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bni">BNI (VA)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bri" value="bank_transfer_bri" {{ old('payment_method')=='bank_transfer_bri' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bri">BRI (VA)</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="permata" value="bank_transfer_permata" {{ old('payment_method')=='bank_transfer_permata' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permata">Permata (VA)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mandiri" value="bank_transfer_mandiri" {{ old('payment_method')=='bank_transfer_mandiri' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mandiri">Mandiri (Billing / E-Channel)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="echannel" value="echannel" {{ old('payment_method')=='echannel' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="echannel">Mandiri Bill / E-Channel</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4">E-Wallet</h6>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="gopay" value="gopay" {{ old('payment_method')=='gopay' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="gopay">GoPay</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="shopeepay" value="shopeepay" {{ old('payment_method')=='shopeepay' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shopeepay">ShopeePay</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="dana" value="dana" {{ old('payment_method')=='dana' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dana">DANA</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4">QR & Lainnya</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="qris" value="qris" {{ old('payment_method')=='qris' ? 'checked' : '' }}>
                            <label class="form-check-label" for="qris">QRIS</label>
                        </div>

                        <h6 class="mt-3">Convenience Store</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="alfamart" value="cstore_alfamart" {{ old('payment_method')=='cstore_alfamart' ? 'checked' : '' }}>
                            <label class="form-check-label" for="alfamart">Alfamart (Kode Bayar)</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="indomaret" value="cstore_indomaret" {{ old('payment_method')=='cstore_indomaret' ? 'checked' : '' }}>
                            <label class="form-check-label" for="indomaret">Indomaret (Kode Bayar)</label>
                        </div>

                        <h6 class="mt-3">Kartu & Cicilan</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card" {{ old('payment_method')=='credit_card' ? 'checked' : '' }}>
                            <label class="form-check-label" for="card">Kartu Kredit/Debit</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="akulaku" value="akulaku" {{ old('payment_method')=='akulaku' ? 'checked' : '' }}>
                            <label class="form-check-label" for="akulaku">Akulaku (Cicilan)</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100">Lanjutkan ke Pembayaran</button>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    @if(count($items) > 0)
                        <ul class="list-unstyled mb-3">
                            @foreach($items as $it)
                                @php
                                    $productName = data_get($it, 'product.nama', data_get($it, 'product_name', 'Produk'));
                                    $unitPrice = data_get($it, 'product.harga', $it['price'] ?? 0);
                                    $qty = $it['quantity'] ?? 1;
                                    $lineTotal = $unitPrice * $qty;
                                @endphp

                                <li class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="me-2">
                                        <div class="fw-medium">{{ $productName }} <small class="text-muted">x {{ $qty }}</small></div>
                                    </div>
                                    <div class="text-end">Rp {{ number_format($lineTotal, 0, ',', '.') }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>Keranjang kosong.</p>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Ongkir:</span>
                        <span>Rp {{ number_format(data_get($shipping, 'cost', 0), 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span class="text-warning">Rp {{ number_format($totalWithShipping ?? ($total ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chk = document.getElementById('use_profile_address');
    const ta = document.getElementById('shipping_address');
    const profileAddress = {!! json_encode(auth()->user()->address ?? '') !!};

    if (chk && ta) {
        chk.addEventListener('change', function () {
            if (this.checked) {
                ta.value = profileAddress;
                ta.setAttribute('readonly', 'readonly');
            } else {
                ta.removeAttribute('readonly');
            }
        });
    }
});
</script>
@endpush
