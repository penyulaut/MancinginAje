@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h3 class="mb-3">Pembayaran dalam Proses</h3>
                <p>Order #{{ $order->id }} — Total: <strong>Rp {{ number_format($order->total_harga,0,',','.') }}</strong></p>

                <p class="text-muted">Pembayaran belum terkonfirmasi. Anda dapat menunggu notifikasi Midtrans, atau cek secara manual apakah pembayaran sudah berhasil.</p>

                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('pages.beranda') }}" class="btn btn-secondary">Kembali ke Beranda</a>
                    <button id="check-payment" class="btn btn-primary">Cek Pembayaran</button>
                    <a href="{{ route('pages.yourorders') }}" class="btn btn-outline-dark">Lihat Pesanan Saya</a>
                </div>

                <div class="mt-4">
                    <p>Status saat ini: <strong id="paymentStatus">{{ $order->payment_status ?? 'pending' }}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('check-payment');
    const statusEl = document.getElementById('paymentStatus');
    btn.addEventListener('click', async function(){
        btn.disabled = true;
        btn.textContent = 'Memeriksa...';
        try {
            const resp = await fetch("{{ route('payment.status', ['id' => $order->id]) }}", { credentials: 'same-origin' });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const data = await resp.json();
            statusEl.textContent = data.payment_status ?? data.status ?? 'unknown';
            if (data.payment_status === 'paid' || data.status === 'completed') {
                // redirect to success page to show invoice
                window.location.href = "{{ route('payment.finish') }}?order_id={{ $order->id }}";
                return;
            }
            alert('Pembayaran belum terkonfirmasi. Silakan tunggu beberapa saat lalu coba lagi.');
        } catch (e) {
            console.warn('Check payment failed', e);
            alert('Gagal memeriksa pembayaran. Coba lagi.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Cek Pembayaran';
        }
    });
});
</script>
@endpush
