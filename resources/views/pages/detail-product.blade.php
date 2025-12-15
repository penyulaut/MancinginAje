@extends('layouts.main')

@section('content')
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-5">
                <div class="sticky-top" style="top: 20px;">
                    <div style="height: 500px; overflow: hidden; background: #f0f0f0; border-radius: 10px;">
                        <img src="{{ asset($product->gambar) }}" class="img-fluid h-100 w-100" style="object-fit: cover;" alt="{{ $product->nama }}">
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('pages.beranda') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pages.orders') }}">Katalog</a></li>
                        <li class="breadcrumb-item active">{{ $product->nama }}</li>
                    </ol>
                </nav>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <h1 class="fw-bold mb-3">{{ $product->nama }}</h1>

                <div class="mb-4">
                    <span class="badge bg-warning mb-3">Kategori: {{ $product->category->nama ?? 'Uncategorized' }}</span>
                </div>

                <h2 class="text-warning fw-bold mb-4">Rp {{ number_format($product->harga, 0, ',', '.') }}</h2>

                <div class="alert alert-info mb-4">
                    <p class="mb-0"><strong>Stok Tersedia:</strong> {{ $product->stok > 0 ? $product->stok . ' unit' : 'Habis' }}</p>
                </div>

                <h5 class="fw-bold mb-3">Deskripsi Produk</h5>
                <p class="text-muted mb-4">{{ $product->deskripsi }}</p>

                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ route('cart.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Jumlah Pesanan</label>
                                <div class="input-group" style="width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stok }}" class="form-control text-center">
                                    <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                                </div>
                                <small class="text-muted">Maksimal: {{ $product->stok }} unit</small>
                            </div>

                            @if($product->stok > 0)
                                <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                    <i class="fas fa-ban"></i> Produk Habis
                                </button>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <a href="{{ route('pages.orders') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Informasi Penting</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Produk Original Bergaransi</li>
                            <li class="mb-2"><i class="fas fa-truck text-success"></i> Gratis Ongkir Pembelian Minimal Rp 100.000</li>
                            <li class="mb-2"><i class="fas fa-redo text-success"></i> Kebijakan Pengembalian 30 Hari</li>
                            <li class="mb-2"><i class="fas fa-headset text-success"></i> Customer Service 24/7</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function increaseQty() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }

        function decreaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
@endsection
