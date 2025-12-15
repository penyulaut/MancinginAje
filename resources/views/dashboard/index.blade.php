@extends('layouts.main')

@section('content')
  <x-navbar/>
  <x-sidebar></x-sidebar>

  <!-- Main Content -->
  <div class="search-bar px-5" style="margin-left: 200px">
    <nav class="navbar p-2 m-2">
      <span class="navbar-brand fw-bold text-light">Dashboard Admin</span>
      <a href="/beranda/dashboard/create" class="btn btn-warning">Tambah data</a>
    </nav>

    <div class="table-container">
      <h5 class="mb-3">Data Produk</h5>

      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>       
      @endif

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                @endforeach
            </ul>
        </div>          
      @endif

      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
            <th>Penjual</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="text-center">
        @foreach ($products as $item)
          <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$item->nama}}</td>
            <td>{{$item->deskripsi}}</td>
            <td>Rp {{$item->harga}}</td>
            <td>{{ $item->stok }}</td>
            <td>{{ optional($item->category)->nama ?? '-' }}</td>
            <td>{{ optional($item->seller)->name ?? '—' }}</td>
            <td>
              <a href="{{ route('dashboard.edit', ['id'=>$item->id]) }}" class="btn btn-sm btn-primary text-light">Edit</a>

              <form method="POST" action="{{ route('dashboard.destroy', ['id' => $item->id]) }}" style="display:inline-block" onsubmit="return confirm('Hapus produk ini?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </td>
          </tr> 
        @endforeach         
        </tbody>        
      </table>
    </div>
  </div>
@endsection