@extends('layouts.main')

@section('content')
<div class="container py-6">
    <h2 class="mb-4">Checkout</h2>

    <form method="POST" action="{{ route('payment.store') }}">
        @csrf

        <div class="card mb-4 p-4">
            <h5>Rincian Pesanan</h5>
            <ul class="list-group list-group-flush mb-3">
                @foreach($items as $it)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $it['product']->nama }}</strong>
                            <div class="text-muted">Jumlah: {{ $it['quantity'] }}</div>
                        </div>
                        <div>Rp {{ number_format($it['product']->harga * $it['quantity'], 0, ',', '.') }}</div>
                    </li>
                @endforeach
            </ul>
            <div class="text-end fw-bold">Total: Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>

        <div class="card p-4 mb-4">
            <h5>Data Pembeli</h5>
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat Pengiriman</label>
                <textarea name="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', auth()->user()->address ?? '') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" class="form-select" required>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="e_wallet">E-Wallet</option>
                    <option value="card">Kartu (Card)</option>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button class="btn btn-primary">Lanjut ke Pembayaran</button>
        </div>
    </form>
</div>
@endsection
@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Pembayaran</h1>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
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
                                           value="{{ old('customer_name', auth()->user()->name) }}" required>
                                    @error('customer_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                           id="customer_email" name="customer_email" 
                                           value="{{ old('customer_email', auth()->user()->email) }}" required>
                                    @error('customer_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                                           id="customer_phone" name="customer_phone" 
                                           value="{{ old('customer_phone', auth()->user()->phone) }}" required>
                                    @error('customer_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Alamat Pengiriman</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                              id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
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
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="bank_transfer" value="bank_transfer" required>
                                    <label class="form-check-label" for="bank_transfer">
                                        Transfer Bank
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="e_wallet" value="e_wallet">
                                    <label class="form-check-label" for="e_wallet">
                                        E-Wallet (GCash, OVO, DANA, LinkAja)
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="card" value="card">
                                    <label class="form-check-label" for="card">
                                        Kartu Kredit/Debit
                                    </label>
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
                            @foreach($cart as $productId => $item)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $item['nama'] }} x {{ $item['quantity'] }}</span>
                                    <span>Rp {{ number_format(($item['harga'] ?? $item['price']) * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span class="text-warning">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
