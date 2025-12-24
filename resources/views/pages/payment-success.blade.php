@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 4.5rem;"></i>
                    </div>
                    <h2 class="text-success mb-2">Pembayaran Berhasil</h2>
                    <p class="text-muted mb-4">Terima kasih, pesanan Anda telah diterima. Di bawah ini adalah ringkasan transaksi.</p>

                    <div class="row mb-3">
                        <div class="col-md-6 text-start">
                            <p class="mb-1"><strong>Nomor Pesanan:</strong><br>#{{ $order->id }}</p>
                            <p class="mb-1"><strong>Nama:</strong><br>{{ $order->customer_name }}</p>
                            <p class="mb-1"><strong>Email:</strong><br>{{ $order->customer_email }}</p>
                        </div>
                        <div class="col-md-6 text-start">
                            <p class="mb-1"><strong>Total Pembayaran:</strong><br><span class="fw-bold">Rp {{ number_format($order->total_harga,0,',','.') }}</span></p>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong><br>{{ $order->payment_method ?? '-' }}</p>
                            <p class="mb-1"><strong>Status Pembayaran:</strong><br>
                                <span class="badge 
                                    @if($order->payment_status == 'paid') bg-success
                                    @elseif($order->payment_status == 'pending') bg-warning text-dark
                                    @elseif($order->payment_status == 'expired') bg-danger
                                    @else bg-secondary @endif">
                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 text-start">
                            <h5 class="mb-2">Detail Transaksi</h5>
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td style="width:200px"><strong>ID Transaksi</strong></td>
                                        <td>{{ $order->transaction_id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Snap Token / Ref</strong></td>
                                        <td>{{ $order->snap_token ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal</strong></td>
                                        <td>{{ $order->updated_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Alamat Pengiriman</strong></td>
                                        <td>{{ $order->shipping_address }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3 text-start">
                        <h5 class="mb-2">Produk & Ringkasan</h5>
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
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product->nama ?? '-' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>Rp {{ number_format($item->price,0,',','.') }}</td>
                                            <td>Rp {{ number_format($item->price * $item->quantity,0,',','.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @php
                                        $productsTotal = $order->items->reduce(function($carry, $it){
                                            return $carry + ($it->price * $it->quantity);
                                        }, 0);
                                        $shippingCost = (float) ($order->shipping_cost ?? 0);
                                    @endphp
                                    <tr>
                                        <td colspan="3" class="text-end">Ongkir:</td>
                                        <td>Rp {{ number_format($shippingCost,0,',','.') }}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">Total:</td>
                                        <td>Rp {{ number_format($productsTotal + $shippingCost,0,',','.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a href="{{ route('pages.beranda') }}" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        <a href="{{ route('pages.yourorders') }}" class="btn btn-primary">Lihat Pesanan Saya</a>
                        @if($order->payment_status != 'paid')
                            <a href="{{ route('payment.retry', $order->id) }}" class="btn btn-warning">Lanjutkan Pembayaran</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
