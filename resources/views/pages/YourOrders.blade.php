@extends('layouts.main')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4">Pesanan Kamu</h2>

    @if ($orders->isEmpty())
        <div class="alert alert-info text-center">
            Kamu belum memiliki pesanan.
        </div>
    @else
        @foreach ($orders as $order)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <span><strong>Order #{{ $order->id }}</strong></span>
                    <span class="badge 
                        @if($order->status == 'pending') bg-secondary
                        @elseif($order->status == 'paid') bg-success
                        @elseif($order->status == 'cancelled') bg-danger
                        @else bg-info @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Total Harga:</strong> Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                    <p><strong>Tanggal Pesanan:</strong> {{ $order->created_at->format('d M Y, H:i') }}</p>

                    <table class="table table-striped mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th>Nama Produk</th>
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
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
