@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Daftar Pesanan Semua Pengguna</h3>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user?->name ?? '—' }}</td>
                    <td>
                        <ul class="mb-0">
                            @foreach($order->items as $it)
                                <li>{{ $it->product?->name ?? 'Produk terhapus' }} × {{ $it->quantity }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>Rp {{ number_format($order->total_harga,0,',','.') }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($order->status !== 'paid')
                            <form action="{{ route('admin.orders.accept', $order->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                <button class="btn btn-sm btn-success">Auto-Acc</button>
                            </form>
                            <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">
                                @csrf
                                <button class="btn btn-sm btn-danger">Batalkan</button>
                            </form>
                        @else
                            <span class="text-success">Lunas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
