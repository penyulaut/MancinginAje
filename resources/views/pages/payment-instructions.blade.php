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
            @php
                $isSandbox = !config('midtrans.is_production');
                $sandboxBaseUrl = 'https://simulator.sandbox.midtrans.com';
            @endphp

            @if(isset($midtrans->va_numbers) && count($midtrans->va_numbers) > 0)
                <h5>Virtual Account</h5>
                @foreach($midtrans->va_numbers as $va)
                    @php
                        $vaNumber = $va->va_number;
                        $bank = strtolower($va->bank);
                        // Midtrans sandbox simulator URLs for VA
                        $simulatorUrl = match($bank) {
                            'bca' => $sandboxBaseUrl . '/bca/va/index',
                            'bni' => $sandboxBaseUrl . '/bni/va/index',
                            'bri' => $sandboxBaseUrl . '/bri/va/index',
                            'permata' => $sandboxBaseUrl . '/permata/va/index',
                            'mandiri' => $sandboxBaseUrl . '/openapi/va/index?bank=mandiri',
                            default => $sandboxBaseUrl . '/openapi/va/index?bank=' . $bank,
                        };
                    @endphp
                    <div class="mb-3 p-3 border rounded bg-light">
                        <div class="fw-bold text-primary">{{ strtoupper($va->bank) }}</div>
                        <div class="text-monospace fs-4 my-2" id="va-number-{{ $loop->index }}">{{ $vaNumber }}</div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $vaNumber }}')">
                                <i class="fas fa-copy me-1"></i> Salin VA
                            </button>
                            @if($isSandbox)
                            <a href="{{ $simulatorUrl }}" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-external-link-alt me-1"></i> Bayar via Simulator
                            </a>
                            @endif
                        </div>
                        @if($isSandbox)
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> 
                            Klik "Bayar via Simulator" lalu masukkan VA: <strong>{{ $vaNumber }}</strong>
                        </small>
                        @endif
                    </div>
                @endforeach
                <p class="small text-muted">Gunakan nomor VA di atas untuk melakukan transfer melalui mobile/ATM/internet banking.</p>

            @elseif(isset($midtrans->payment_code))
                @php
                    $paymentCode = $midtrans->payment_code;
                    $store = str_contains($method, 'alfamart') ? 'alfamart' : (str_contains($method, 'indomaret') ? 'indomaret' : 'cstore');
                    $storeSimulatorUrl = match($store) {
                        'alfamart' => $sandboxBaseUrl . '/alfamart/index',
                        'indomaret' => $sandboxBaseUrl . '/indomaret/index',
                        default => $sandboxBaseUrl . '/cstore/index',
                    };
                @endphp
                <h5>Kode Pembayaran</h5>
                <div class="p-3 border rounded bg-light mb-3">
                    <div class="text-monospace fs-4 my-2" id="payment-code">{{ $paymentCode }}</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $paymentCode }}')">
                            <i class="fas fa-copy me-1"></i> Salin Kode
                        </button>
                        @if($isSandbox)
                        <a href="{{ $storeSimulatorUrl }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="fas fa-external-link-alt me-1"></i> Bayar via Simulator
                        </a>
                        @endif
                    </div>
                    @if($isSandbox)
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i> 
                        Klik "Bayar via Simulator" lalu masukkan kode: <strong>{{ $paymentCode }}</strong>
                    </small>
                    @endif
                </div>
                <p class="small text-muted">Gunakan kode di kasir untuk menyelesaikan pembayaran.</p>

            @elseif(isset($midtrans->payment_key))
                <h5>Kode Pembayaran</h5>
                <div class="p-3 border rounded bg-light mb-3">
                    <div class="text-monospace fs-4 my-2">{{ $midtrans->payment_key }}</div>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $midtrans->payment_key }}')">
                        <i class="fas fa-copy me-1"></i> Salin Kode
                    </button>
                </div>
                <p class="small text-muted">Gunakan kode di kasir untuk menyelesaikan pembayaran.</p>

            @elseif(isset($midtrans->actions) && is_array($midtrans->actions))
                <h5>Instruksi Pembayaran</h5>
                <div class="list-group mb-3">
                    @foreach($midtrans->actions as $act)
                        @php
                            $actionUrl = $act->url ?? '#';
                            $actionName = $act->name ?? 'Aksi';
                        @endphp
                        <a href="{{ $actionUrl }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-external-link-alt me-2"></i>{{ $actionName }}</span>
                            <span class="badge bg-success">Buka</span>
                        </a>
                    @endforeach
                </div>

            @elseif(isset($midtrans->qr_string) || isset($midtrans->qr_code))
                @php
                    $qrData = $midtrans->qr_string ?? $midtrans->qr_code ?? '';
                    // Generate QR Code image URL using Google Charts API
                    $qrImageUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($qrData);
                    $qrisSimulatorUrl = $sandboxBaseUrl . '/qris/index';
                @endphp
                <h5>QRIS</h5>
                <div class="text-center p-3 border rounded bg-light mb-3">
                    <img src="{{ $qrImageUrl }}" alt="QRIS Code" class="img-fluid mb-3" style="max-width: 250px;">
                    <div class="text-monospace small text-break mb-2" style="word-break: break-all;">{{ Str::limit($qrData, 50) }}</div>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $qrData }}')">
                            <i class="fas fa-copy me-1"></i> Salin QRIS String
                        </button>
                        @if($isSandbox)
                        <a href="{{ $qrisSimulatorUrl }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="fas fa-external-link-alt me-1"></i> Bayar via Simulator
                        </a>
                        @endif
                    </div>
                    @if($isSandbox)
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i> 
                        Scan QR code di atas atau klik "Bayar via Simulator" untuk testing
                    </small>
                    @endif
                </div>
                <p class="small text-muted">Scan QR code di atas menggunakan aplikasi e-wallet Anda (GoPay, OVO, DANA, dll).</p>

            @else
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Tunggu beberapa saat, instruksi pembayaran sedang disiapkan.
                </div>
            @endif
        </div>
    </div>

    <!-- Payment status card -->
    <div class="card p-4 mb-4">
        <h5>Status Pembayaran</h5>
        <p>Status: <strong id="payment-status">{{ $order->payment_status ?? 'pending' }}</strong></p>
        <button id="check-status-btn" class="btn btn-primary btn-sm" onclick="checkPaymentStatus()">
            <i class="fas fa-sync-alt me-1"></i> Cek Status Pembayaran
        </button>
    </div>

    <div class="d-flex justify-content-between flex-wrap gap-2">
        <a href="{{ route('pages.beranda') }}" class="btn btn-secondary">Kembali ke Beranda</a>
        <a href="{{ route('payment.finish') }}?order_id={{ $order->id }}" class="btn btn-success">Selesai / Cek Status</a>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Berhasil disalin: ' + text);
    }).catch(err => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Berhasil disalin: ' + text);
    });
}

async function checkPaymentStatus() {
    const btn = document.getElementById('check-status-btn');
    const statusEl = document.getElementById('payment-status');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memeriksa...';
    
    try {
        const resp = await fetch("{{ route('payment.status', ['id' => $order->id]) }}", { credentials: 'same-origin' });
        const data = await resp.json();
        statusEl.textContent = data.payment_status ?? data.status ?? 'unknown';
        
        if (data.payment_status === 'paid' || data.status === 'paid') {
            alert('Pembayaran berhasil! Mengalihkan ke halaman sukses...');
            window.location.href = "{{ route('payment.finish') }}?order_id={{ $order->id }}";
        } else {
            alert('Status saat ini: ' + (data.payment_status ?? 'pending'));
        }
    } catch (e) {
        console.error('Check status error:', e);
        alert('Gagal memeriksa status. Coba lagi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Cek Status Pembayaran';
    }
}
</script>
@endpush
@endsection
