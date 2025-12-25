@extends('layouts.main')

@section('content')
<div class="container py-6">
    <h2>Pembayaran</h2>

    <div class="card p-4 mb-4">
        <h5>Order #{{ $order->id }}</h5>
        <p>Total: <strong>Rp {{ number_format($order->total_harga,0,',','.') }}</strong></p>
    </div>

    <div class="text-center">
        <button id="pay-button" class="btn btn-success">Bayar Sekarang</button>
    </div>

    @php
        $midtransHost = config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $midtransHost }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        const snapToken = "{{ $snapToken }}";
        function startPolling() {
            const orderId = {{ $order->id }};
            const pollUrl = "{{ url('/beranda/api/order-status') }}/" + orderId;
            let attempts = 0;
            const iv = setInterval(async function(){
                attempts++;
                try {
                    const resp = await fetch(pollUrl, { credentials: 'same-origin' });
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const data = await resp.json();
                    if (data.payment_status === 'paid' || data.status === 'paid') {
                        clearInterval(iv);
                        // clear cart server-side and update UI without a full reload
                        try {
                            fetch("{{ url('/beranda/api/order-clear') }}/" + orderId, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }})
                            .then(r => r.json()).then(res => {
                                if (res.cleared) {
                                    document.getElementById('pay-button').disabled = true;
                                    document.getElementById('pay-button').textContent = 'Pembayaran dikonfirmasi';
                                }
                                alert('Pembayaran dikonfirmasi. Terima kasih!');
                            }).catch(()=>{
                                // fallback: redirect if clearing failed
                                window.location.href = "{{ route('payment.finish') }}?order_id={{ $order->id }}";
                            });
                        } catch(e) {
                            window.location.href = "{{ route('payment.finish') }}?order_id={{ $order->id }}";
                        }
                        return;
                    }
                    // stop polling after 60 attempts (~3 minutes)
                    if (attempts > 60) {
                        clearInterval(iv);
                        alert('Masih menunggu konfirmasi pembayaran. Periksa halaman pesanan Anda.');
                    }
                } catch (e) {
                    // ignore transient errors
                    console.warn('Polling error', e);
                }
            }, 3000);
        }

        document.getElementById('pay-button').addEventListener('click', function () {
            window.snap.pay(snapToken, {
                onSuccess: function(result){
                    // POST the result to server so backend can refresh status immediately
                    (async function(){
                        try {
                            await fetch("{{ route('payment.snap.callback') }}", {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(result)
                            });
                        } catch (e) {
                            console.warn('Failed to notify server about snap success', e);
                        }
                        // start polling as fallback
                        startPolling();
                        alert('Pembayaran diproses. Menunggu konfirmasi dari Midtrans. Halaman akan otomatis diperbarui.');
                    })();
                },
                onPending: function(result){
                    // notify server as well so we can persist transaction id
                    (async function(){
                        try {
                            await fetch("{{ route('payment.snap.callback') }}", {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(result)
                            });
                        } catch (e) {
                            console.warn('Failed to notify server about snap pending', e);
                        }
                        startPolling();
                        alert('Pembayaran pending. Menunggu konfirmasi dari Midtrans.');
                    })();
                },
                onError: function(result){
                    alert('Pembayaran gagal.');
                }
            });
        });
    </script>
    <!-- Pusher client for real-time updates (fallback to direct Pusher JS) -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <script>
        // Initialize Pusher client so frontend receives OrderStatusUpdated events
        (function(){
            try {
                const orderId = {{ $order->id }};
                const pusherKey = '{{ env('PUSHER_APP_KEY') }}';
                const cluster = '{{ env('PUSHER_APP_CLUSTER') }}';
                if (pusherKey) {
                    const pusher = new Pusher(pusherKey, {
                        cluster: cluster || 'mt1',
                        forceTLS: true
                    });
                    Pusher.logToConsole = true;
                    const channelName = 'orders.' + orderId;
                    console.info('Pusher init', { key: pusherKey, cluster: cluster, channel: channelName });
                    const channel = pusher.subscribe(channelName);
                    channel.bind('OrderStatusUpdated', function(data) {
                        console.info('Pusher event received', data);
                        // update DOM elements when payment becomes paid/completed
                        const statusText = document.getElementById('paymentStatusText');
                        const txEl = document.getElementById('transactionId');
                        if (data.transaction_id) txEl.textContent = data.transaction_id;
                        statusText.textContent = data.payment_status ? data.payment_status : data.status;
                        if (data.payment_status === 'paid' || data.status === 'completed') {
                            document.getElementById('pay-button').disabled = true;
                            document.getElementById('pay-button').textContent = 'Pembayaran dikonfirmasi';
                            try { alert('Pembayaran terkonfirmasi (realtime).'); } catch(e){}
                        }
                    });

                    pusher.connection.bind('state_change', function(states) {
                        console.info('Pusher connection state change', states);
                        const el = document.getElementById('paymentStatusText');
                        if (el) el.dataset.pusherState = states.current;
                    });

                    pusher.connection.bind('error', function(err) {
                        console.error('Pusher connection error', err);
                    });
                }
            } catch (e) {
                console.warn('Pusher init failed', e);
            }
        })();
    </script>
</div>
@endsection
