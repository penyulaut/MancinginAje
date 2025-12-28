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

                @php
                    $isSandbox = !config('midtrans.is_production');
                @endphp

                @if($isSandbox)
                <div class="alert alert-info mt-4">
                    <h6 class="alert-heading"><i class="fas fa-flask me-2"></i> Mode Sandbox (Testing)</h6>
                    <p class="mb-2 small">Untuk testing pembayaran, gunakan simulator Midtrans:</p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <a href="https://simulator.sandbox.midtrans.com/bca/va/index" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-university me-1"></i> VA BCA
                        </a>
                        <a href="https://simulator.sandbox.midtrans.com/bni/va/index" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-university me-1"></i> VA BNI
                        </a>
                        <a href="https://simulator.sandbox.midtrans.com/bri/va/index" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-university me-1"></i> VA BRI
                        </a>
                        <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-qrcode me-1"></i> QRIS
                        </a>
                        <a href="https://simulator.sandbox.midtrans.com/gopay/ui/index" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-wallet me-1"></i> GoPay
                        </a>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Masukkan nomor VA atau kode pembayaran Anda di simulator untuk menyelesaikan testing.
                    </small>
                </div>
                @endif
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
            alert('Gagal memeriksa pembayaran. Pastikan koneksi internet stabil dan coba lagi. Error: ' + (e.message || 'Unknown'));
        } finally {
            btn.disabled = false;
            btn.textContent = 'Cek Pembayaran';
        }
    });
});
</script>
@endpush
