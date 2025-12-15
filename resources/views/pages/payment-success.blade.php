@extends('layouts.main')

@section('content')
<div class="container py-6 text-center">
    <h2>Pembayaran Berhasil</h2>
    <p>Terima kasih! Pembayaran Anda telah diproses. Silakan cek halaman pesanan Anda untuk detailnya.</p>
    <a href="{{ route('pages.yourorders') }}" class="btn btn-primary">Lihat Pesanan Saya</a>
</div>
@endsection
@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h1 class="mb-3 text-success">Pembayaran Berhasil!</h1>
                    <p class="text-muted mb-4">Terima kasih telah berbelanja di MancinginAje. Pesanan Anda telah kami terima.</p>
                    
                    <div class="alert alert-info">
                        <strong>Nomor Pesanan:</strong> #{{ $order->id }}
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 text-start">
                            <p><strong>Nama Penerima:</strong><br>{{ $order->customer_name }}</p>
                            <p><strong>Email:</strong><br>{{ $order->customer_email }}</p>
                        </div>
                        <div class="col-md-6 text-start">
                            <p><strong>Nomor Telepon:</strong><br>{{ $order->customer_phone }}</p>
                            <p><strong>Total Pembayaran:</strong><br><span class="text-warning fw-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span></p>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Alamat Pengiriman:</strong><br>{{ $order->shipping_address }}
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5>Detail Produk</h5>
                        <table class="table table-sm">
                            <thead>
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
                                    <td>{{ $item->product->nama ?? 'Produk' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3">Total:</td>
                                    <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-muted mb-4">Email konfirmasi telah dikirim ke {{ $order->customer_email }}. Pesanan Anda akan segera diproses dan dikirimkan.</p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('pages.beranda') }}" class="btn btn-warning btn-lg">Kembali ke Beranda</a>
                        <a href="{{ route('pages.yourorders') }}" class="btn btn-outline-warning btn-lg">Lihat Pesanan Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
