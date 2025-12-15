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

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        const snapToken = "{{ $snapToken }}";
        document.getElementById('pay-button').addEventListener('click', function () {
            window.snap.pay(snapToken, {
                onSuccess: function(result){
                    window.location.href = "{{ route('payment.finish') }}";
                },
                onPending: function(result){
                    window.location.href = "{{ route('payment.finish') }}";
                },
                onError: function(result){
                    alert('Pembayaran gagal.');
                }
            });
        });
    </script>
</div>
@endsection
