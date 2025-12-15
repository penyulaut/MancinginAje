@extends('layouts.main')

@section('content')  
    <x-navbar/>

    {{-- Search Start--}}
    <div class="search-bar container">
      <form class="d-flex" action="{{ route('pages.orders') }}" method="GET">
        <input 
          class="form-control me-2" 
          type="search" 
          name="search" 
          placeholder="Cari produk..."
          value="{{ request('search') }}"
        >
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
    {{-- Search End --}}

    {{-- Tab Orders Start--}}
    <div class="container mt-5 mb-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">Katalog Produk</h2>
                <p class="text-muted">Temukan peralatan pancing terbaik untuk petualangan memancing Anda</p>
            </div>
        </div>

        {{-- Search Start--}}
        <div class="mb-4">
            <form class="d-flex gap-2" action="{{ route('pages.orders') }}" method="GET">
                <input 
                    class="form-control" 
                    type="search" 
                    name="search" 
                    placeholder="Cari produk..."
                    value="{{ request('search') }}"
                >
                <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>
        {{-- Search End --}}

        <ul class="nav nav-pills mb-4 flex-wrap" id="categoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab" aria-controls="pills-all" aria-selected="true">
                    <i class="fas fa-th"></i> Semua Produk
                </button>
            </li>
            @foreach($categories as $category)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-{{ $category->id }}-tab" data-bs-toggle="pill" data-bs-target="#pills-{{ $category->id }}" type="button" role="tab" aria-controls="pills-{{ $category->id }}" aria-selected="false">
                    {{ $category->nama }}
                </button>
            </li>
            @endforeach
        </ul>

        @if($products->isEmpty() && !request('search'))
            <div class="alert alert-info text-center py-5">
                <p class="fs-5">Belum ada produk tersedia 😢</p>
            </div>
        @endif   

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">
                <div class="row g-4">
                    @forelse($products as $item)            
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 shadow-sm border-0 product-card">
                                <div style="height: 250px; overflow: hidden; background: #f0f0f0;">
                                    <img src="{{ asset($item->gambar) }}" class="card-img-top h-100" style="object-fit: cover; width: 100%;" alt="{{ $item->nama }}">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                                    <p class="card-text text-muted flex-grow-1">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h6 mb-0 text-warning fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        <span class="badge bg-success">Stok: {{ $item->stok }}</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top">
                                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-sm btn-warning w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>   
                    @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">Produk tidak ditemukan</p>
                        </div>
                    @endforelse                 
                </div>         
            </div>

            @foreach($categories as $category)
            <div class="tab-pane fade" id="pills-{{ $category->id }}" role="tabpanel" aria-labelledby="pills-{{ $category->id }}-tab">
                <div class="row g-4">
                    @forelse($category->products as $item)
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 shadow-sm border-0 product-card">
                                <div style="height: 250px; overflow: hidden; background: #f0f0f0;">
                                    <img src="{{ asset($item->gambar) }}" class="card-img-top h-100" style="object-fit: cover; width: 100%;" alt="{{ $item->nama }}">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                                    <p class="card-text text-muted flex-grow-1">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h6 mb-0 text-warning fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        <span class="badge bg-success">Stok: {{ $item->stok }}</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top">
                                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-sm btn-warning w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>   
                    @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">Belum ada produk di kategori ini</p>
                        </div>
                    @endforelse
                </div>     
            </div>
            @endforeach
        </div>               

    </div>

    <style>
        .product-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .nav-link {
            color: #666;
            border-radius: 5px;
            margin-right: 5px;
        }
        
        .nav-link.active {
            background-color: #FFC107 !important;
            color: white !important;
        }
        
        .nav-link:hover {
            background-color: #FFE080;
        }
    </style>
    {{-- Tab Orders End --}}
   
    </div>
@endsection