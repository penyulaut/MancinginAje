@extends('layouts.main')

@section('content')
    <x-navbar/>
    <div class="container mt-4">
        
        <h3 class="search-bar">Keranjang Belanja</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(count($cart) > 0)
            <table class="table table-bordered mt-3">
                <thead class="table-light">
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart as $id => $item)
                        <tr>
                            <td><img src="{{ asset($item['gambar']) }}" width="80"></td>
                            <td>{{ $item['nama'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item['harga'] * $item['quantity'], 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('cart.destroy', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $id }}">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>                        
                    @endforeach
                </tbody>
            </table>        
            <h4 class="mt-3">Total Belanja: Rp {{ number_format($total, 0, ',', '.') }}</h4>

            <form action="{{ route('orders.store' ) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning">Checkout</button>
                <a href="/beranda/orders" class="btn btn-primary">Pesan Lagi</a>
            </form>
        @else
            <p>Keranjang masih kosong.</p>
        @endif
    </div>
@endsection
