@extends('layouts.main')

@section('content')
    <div class="container mt-4 mb-5">
        
        <h3 class="mb-4">Keranjang Belanja</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(count($items) > 0)
            <div class="row">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-warning">
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            @if($item['product']->gambar)
                                                <img src="{{ asset($item['product']->gambar) }}" width="80" alt="{{ $item['product']->nama }}" class="rounded">
                                            @else
                                                <img src="https://via.placeholder.com/80" width="80" alt="No Image" class="rounded">
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item['product']->nama }}</strong><br>
                                            <small class="text-muted">{{ $item['product']->deskripsi }}</small>
                                        </td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('cart.update', $item['product_id']) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->stok }}" class="form-control" style="width: 70px;">
                                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            </form>
                                        </td>
                                        <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('cart.destroy', $item['product_id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>                        
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card sticky-top">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Ringkasan Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ongkir:</span>
                                    <span>Rp 0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total:</span>
                                    <span class="text-warning">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @auth
                                <a href="{{ route('payment.index') }}" class="btn btn-warning w-100 mb-2">Lanjut ke Pembayaran</a>
                            @else
                                <a href="{{ route('login.show') }}" class="btn btn-warning w-100 mb-2">Login untuk Checkout</a>
                            @endauth
                            
                            <a href="{{ route('pages.orders') }}" class="btn btn-outline-secondary w-100">Lanjut Belanja</a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center py-5">
                <h5>Keranjang Anda masih kosong</h5>
                <p class="mb-3">Mulai belanja sekarang dan tambahkan produk ke keranjang</p>
                <a href="{{ route('pages.orders') }}" class="btn btn-warning">Belanja Sekarang</a>
            </div>
        @endif
    </div>
@endsection
