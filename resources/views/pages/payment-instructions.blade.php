@extends('layouts.main')

@section('content')
<div class="container py-6">
    <h2>Instruksi Pembayaran</h2>

    <div class="card p-4 mb-4">
        <h5>Order #{{ $order->id }}</h5>
        <p>Total: <strong>Rp {{ number_format($order->total_harga,0,',','.') }}</strong></p>
        <p>Metode: <strong>{{ strtoupper(str_replace('_', ' ', $method)) }}</strong></p>
    </div>

    <div class="card p-4 mb-4">
        <div class="card-body">
            @if(isset($midtrans->va_numbers) && count($midtrans->va_numbers) > 0)
                <h5>Virtual Account</h5>
                @foreach($midtrans->va_numbers as $va)
                    <div class="mb-2">
                        <div class="fw-bold">{{ strtoupper($va->bank) }}</div>
                        <div class="text-monospace">{{ $va->va_number }}</div>
                    </div>
                @endforeach
                <p class="small text-muted">Gunakan nomor VA di atas untuk melakukan transfer melalui mobile/ATM/internet banking.</p>
            @elseif(isset($midtrans->payment_code))
                <h5>Kode Pembayaran</h5>
                <div class="text-monospace mb-2">{{ $midtrans->payment_code }}</div>
                <p class="small text-muted">Gunakan kode di kasir untuk menyelesaikan pembayaran.</p>
            @elseif(isset($midtrans->payment_key))
                <h5>Kode Pembayaran</h5>
                <div class="text-monospace mb-2">{{ $midtrans->payment_key }}</div>
                <p class="small text-muted">Gunakan kode di kasir untuk menyelesaikan pembayaran.</p>
            @elseif(isset($midtrans->actions) && is_array($midtrans->actions))
                <h5>Instruksi Lainnya</h5>
                <ul>
                    @foreach($midtrans->actions as $act)
                        <li><a href="{{ $act->url ?? '#' }}" target="_blank">{{ $act->name ?? 'Aksi' }}</a></li>
                    @endforeach
                </ul>
            @elseif(isset($midtrans->qr_string) || isset($midtrans->qr_code))
                <h5>QRIS</h5>
                <p class="text-monospace">{{ $midtrans->qr_string ?? $midtrans->qr_code ?? 'Scan QR di aplikasi e-wallet Anda' }}</p>
            @else
                <p>Tunggu beberapa saat, instruksi pembayaran sedang disiapkan.</p>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('pages.beranda') }}" class="btn btn-secondary">Kembali ke Beranda</a>
        <a href="{{ route('payment.finish') }}?order_id={{ $order->id }}" class="btn btn-success">Selesai / Cek Status</a>
    </div>
</div>
@endsection
