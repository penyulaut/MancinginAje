@extends('layouts.main')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="mb-4">Proses Pembayaran</h1>
            
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Order #{{ $order->id }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total Pembayaran:</strong> Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                    <p><strong>Nama:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p><strong>Status Pembayaran:</strong> <span class="badge bg-warning">{{ $order->payment_status }}</span></p>
                </div>
            </div>

            <div id="snap-container"></div>

            <div class="mt-4">
                <p class="text-muted">
                    Halaman pembayaran akan dimuat di bawah. Pilih metode pembayaran dan ikuti instruksi untuk menyelesaikan pembayaran.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans Snap SDK -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    snap.embed('{{ $snapToken }}', {
        embedId: 'snap-container'
    });
</script>
@endsection
