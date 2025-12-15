@extends('layouts.main')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col">
            <h2 class="fw-bold mb-4">Pesanan Saya</h2>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div class="alert alert-info text-center py-5">
            <h5>Belum ada pesanan</h5>
            <p class="mb-3">Anda belum membuat pesanan. Mulai berbelanja sekarang!</p>
            <a href="{{ route('pages.orders') }}" class="btn btn-warning">Belanja Sekarang</a>
        </div>
    @else
        @foreach ($orders as $order)
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="row text-white align-items-center">
                        <div class="col-md-4">
                            <h6 class="mb-1">Pesanan #{{ $order->id }}</h6>
                            <small>{{ $order->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-0"><strong>Total:</strong> Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge 
                                @if($order->payment_status == 'pending') bg-warning
                                @elseif($order->payment_status == 'paid') bg-success
                                @elseif($order->payment_status == 'expired') bg-danger
                                @else bg-secondary @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                            <span class="badge 
                                @if($order->status == 'pending') bg-secondary
                                @elseif($order->status == 'paid') bg-info
                                @elseif($order->status == 'completed') bg-success
                                @else bg-danger @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Informasi Pengiriman</h6>
                            <p class="mb-1"><strong>Nama:</strong> {{ $order->customer_name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                            <p class="mb-1"><strong>Telepon:</strong> {{ $order->customer_phone }}</p>
                            <p><strong>Alamat:</strong> {{ $order->shipping_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Metode Pembayaran</h6>
                            <p class="mb-3">
                                @if($order->payment_method == 'bank_transfer')
                                    <i class="fas fa-university"></i> Transfer Bank
                                @elseif($order->payment_method == 'e_wallet')
                                    <i class="fas fa-wallet"></i> E-Wallet
                                @elseif($order->payment_method == 'card')
                                    <i class="fas fa-credit-card"></i> Kartu Kredit/Debit
                                @else
                                    {{ ucfirst($order->payment_method) }}
                                @endif
                            </p>
                            
                            @if($order->transaction_id)
                                <p><strong>ID Transaksi:</strong> {{ $order->transaction_id }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Detail Produk</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->nama ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold border-top">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                @if($order->payment_status == 'paid')
                                    Pesanan Anda telah dibayar. Sedang diproses untuk pengiriman.
                                @elseif($order->payment_status == 'pending')
                                    Menunggu konfirmasi pembayaran...
                                @elseif($order->payment_status == 'expired')
                                    Waktu pembayaran telah berakhir. Silakan lakukan pesanan baru.
                                @else
                                    Pesanan Anda sedang diproses.
                                @endif
                            </small>
                        </div>
                        <div class="col-auto">
                            @if($order->payment_status == 'pending' && $order->status == 'pending')
                                <a href="{{ route('payment.index') }}" class="btn btn-sm btn-warning">Lanjutkan Pembayaran</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if($orders instanceof \Illuminate\Pagination\Paginator)
            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    @endif

    <div class="mt-4">
        <a href="{{ route('pages.orders') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Lanjut Belanja
        </a>
    </div>
