@extends('layouts.main')

@section('content')
    <x-navbar/>
    <div class="container">
        <div class=" search-bar card shadow-lg border-0">
            <div class="card-header bg-warning text-dark text-center">
                <h3 class="mb-0">{{ isset($productsDetail)? 'Edit Produk': 'Tambah produk'}}</h3>
            </div>

            <div class="card-body p-4">
                <form action="{{ isset($productsDetail)? route('dashboard.update', ['id'=>$productsDetail->id]): route('dashboard.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if (isset($productsDetail))
                        @method('put')
                    @endif
                    <!-- Nama Produk -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $productsDetail->nama??'') }}" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required>{{ old('deskripsi', $productsDetail->deskripsi ?? '') }}</textarea>
                    </div>

                    <!-- Harga -->
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', $productsDetail->harga??'') }}" required>
                    </div>

                    <!-- Stok -->
                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="{{ old('stok', $productsDetail->stok??'') }}" required>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled>Pilih kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $productsDetail->category_id?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Gambar -->
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Upload Gambar Produk</label>
                        <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*">

                        @if (!empty($productsDetail->gambar))
                            <div class="mt-2">
                                <p>Gambar saat ini:</p>
                                <img src="{{ asset($productsDetail->gambar?? '') }}" alt="{{ $productsDetail->nama }}" width="150" class="rounded">
                            </div>
                        @endif
                    </div>
                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard.index') }}" class="btn btn-secondary px-4">Kembali</a>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection