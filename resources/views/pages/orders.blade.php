@extends('layouts.main')

@section('content')  
    <x-navbar/>

    <div class="container" style="margin-top: 100px; min-height: 70vh;">
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-2">Katalog Produk</h2>
                        <p class="text-muted mb-0">Temukan peralatan pancing terbaik untuk petualangan Anda</p>
                    </div>
                </div>

                {{-- Search Bar Modern --}}
                <div class="mb-4">
                    <form action="{{ route('pages.orders') }}" method="GET">
                        <div class="input-group modern-search-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input 
                                class="form-control border-start-0 ps-0" 
                                type="search" 
                                name="search" 
                                placeholder="Cari produk berdasarkan nama atau kategori..."
                                value="{{ request('search') }}"
                            >
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Category Tabs --}}
        <ul class="nav nav-pills mb-4 flex-wrap gap-2" id="categoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button">
                    <i class="fas fa-th me-2"></i>Semua Produk
                </button>
            </li>
            @foreach($categories as $category)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-{{ $category->id }}-tab" data-bs-toggle="pill" data-bs-target="#pills-{{ $category->id }}" type="button">
                    {{ $category->nama }}
                </button>
            </li>
            @endforeach
        </ul>

        @if($products->isEmpty() && !request('search'))
            <div class="alert alert-info border-0 shadow-sm text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3 text-primary"></i>
                <p class="fs-5 mb-0">Belum ada produk tersedia</p>
            </div>
        @endif   

        <div class="tab-content" id="pills-tabContent">
            {{-- All Products Tab --}}
            <div class="tab-pane fade show active" id="pills-all" role="tabpanel">
                <div class="row g-4">
                    @forelse($products as $item)            
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="product-card-modern h-100">
                                <div class="product-image-wrapper">
                                    <img src="{{ asset($item->gambar) }}" class="product-image" alt="{{ $item->nama }}" loading="lazy">
                                    <div class="product-overlay">
                                        <a href="{{ route('products.show', $item->id) }}" class="btn btn-light btn-sm rounded-pill px-4">
                                            <i class="fas fa-eye me-2"></i>Detail
                                        </a>
                                    </div>
                                    @if($item->stok < 10 && $item->stok > 0)
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Stok Terbatas
                                        </span>
                                    @elseif($item->stok == 0)
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-times-circle me-1"></i>Habis
                                        </span>
                                    @endif
                                </div>
                                <div class="product-body p-3">
                                    <h5 class="product-title fw-bold mb-2">{{ $item->nama }}</h5>
                                    <p class="product-description text-muted small mb-3">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="product-price fw-bold text-primary fs-5">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        <span class="badge bg-success-soft text-success">Stok: {{ $item->stok }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted fs-5">Produk tidak ditemukan</p>
                        </div>
                    @endforelse                 
                </div>         
            </div>

            {{-- Category Tabs --}}
            @foreach($categories as $category)
            <div class="tab-pane fade" id="pills-{{ $category->id }}" role="tabpanel">
                <div class="row g-4">
                    @forelse($category->products as $item)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="product-card-modern h-100">
                                <div class="product-image-wrapper">
                                    <img src="{{ asset($item->gambar) }}" class="product-image" alt="{{ $item->nama }}" loading="lazy">
                                    <div class="product-overlay">
                                        <a href="{{ route('products.show', $item->id) }}" class="btn btn-light btn-sm rounded-pill px-4">
                                            <i class="fas fa-eye me-2"></i>Detail
                                        </a>
                                    </div>
                                    @if($item->stok < 10 && $item->stok > 0)
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Stok Terbatas
                                        </span>
                                    @elseif($item->stok == 0)
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            <i class="fas fa-times-circle me-1"></i>Habis
                                        </span>
                                    @endif
                                </div>
                                <div class="product-body p-3">
                                    <h5 class="product-title fw-bold mb-2">{{ $item->nama }}</h5>
                                    <p class="product-description text-muted small mb-3">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="product-price fw-bold text-primary fs-5">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        <span class="badge bg-success-soft text-success">Stok: {{ $item->stok }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted fs-5">Belum ada produk di kategori ini</p>
                        </div>
                    @endforelse
                </div>     
            </div>
            @endforeach
        </div>               
    </div>
@endsection
    {{-- Tab Orders End --}}
    
    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
   
    </div>
@endsection