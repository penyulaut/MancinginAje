@extends('layouts.main')

@section('content')

    <h2>Tambah Produk</h2>

    <!-- Tampilkan pesan sukses -->
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{route ('dashboard.store')}}" method="POST">
        @csrf
        <label>Nama Produk:</label><br>
        <input type="text" name="nama" value="{{ old('nama') }}"><br><br>

        <label>Harga:</label><br>
        <input type="number" step="0.01" name="harga" value="{{ old('harga') }}"><br><br>

        <label>Deskripsi:</label><br>
        <textarea name="deskripsi">{{ old('deskripsi') }}</textarea><br><br>

        <button type="submit">Simpan</button>
    </form>
@endsection