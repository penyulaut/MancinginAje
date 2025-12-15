@extends('layouts.main')

@section('content')  
    <x-navbar/>

    <div class="container products-section" style="margin-top: 100px; min-height: 70vh;">
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-2">Katalog Produk</h2>
                        @if(request('search'))
                            <p class="text-muted mb-0">
                                Menampilkan <strong>{{ $products->total() }}</strong> hasil untuk "<strong>{{ request('search') }}</strong>"
                                @if($products->total() == 0)
                                    <span class="text-danger"> - Tidak ada produk ditemukan</span>
                                @endif
                            </p>
                        @else
                            <p class="text-muted mb-0">Temukan peralatan pancing terbaik untuk petualangan Anda</p>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('pages.orders') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Tampilkan Semua
                        </a>
                    @endif
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

        {{-- Pagination Modern --}}
        @if($products->hasPages())
        <div class="modern-pagination-container mt-5 mb-4">
            <div class="pagination-info text-center mb-3">
                <span class="text-muted">
                    Menampilkan {{ $products->firstItem() }}-{{ $products->lastItem() }} dari {{ $products->total() }} produk
                </span>
            </div>

            <nav aria-label="Product pagination">
                <ul class="pagination justify-content-center modern-pagination">
                    {{-- Previous Page Link --}}
                    @if ($products->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-chevron-left"></i>
                                <span class="d-none d-sm-inline ms-1">Sebelumnya</span>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">
                                <i class="fas fa-chevron-left"></i>
                                <span class="d-none d-sm-inline ms-1">Sebelumnya</span>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @php
                        $start = max(1, $products->currentPage() - 2);
                        $end = min($products->lastPage(), $products->currentPage() + 2);
                    @endphp

                    {{-- First page if not in range --}}
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $products->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    {{-- Page range --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $products->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor

                    {{-- Last page if not in range --}}
                    @if($end < $products->lastPage())
                        @if($end < $products->lastPage() - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $products->url($products->lastPage()) }}">{{ $products->lastPage() }}</a>
                        </li>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($products->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">
                                <span class="d-none d-sm-inline me-1">Selanjutnya</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                <span class="d-none d-sm-inline me-1">Selanjutnya</span>
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>

            {{-- Jump to Page (Optional) --}}
            @if($products->lastPage() > 5)
            <div class="jump-to-page text-center mt-3">
                <form class="d-inline-flex align-items-center" method="GET" action="{{ route('pages.orders') }}">
                    @foreach(request()->query() as $key => $value)
                        @if($key !== 'page')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label class="me-2 text-muted small">Loncat ke halaman:</label>
                    <input type="number" name="page" min="1" max="{{ $products->lastPage() }}"
                           value="{{ $products->currentPage() }}" class="form-control form-control-sm me-2"
                           style="width: 70px;">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
                </form>
            </div>
            @endif
        </div>
        @endif
    </div>
@endsection

{{-- Pagination Smooth Scroll Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to top when pagination links are clicked
    document.querySelectorAll('.modern-pagination .page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only scroll if it's not the current page
            if (!this.parentElement.classList.contains('active') && !this.parentElement.classList.contains('disabled')) {
                // Scroll to the top of the products section
                const productsSection = document.querySelector('.products-section');
                if (productsSection) {
                    productsSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Handle jump to page form
    const jumpForm = document.querySelector('.jump-to-page form');
    if (jumpForm) {
        jumpForm.addEventListener('submit', function(e) {
            const pageInput = this.querySelector('input[name="page"]');
            const page = parseInt(pageInput.value);
            const maxPage = parseInt(pageInput.getAttribute('max'));

            if (page < 1 || page > maxPage) {
                e.preventDefault();
                alert(`Halaman harus antara 1 dan ${maxPage}`);
                return false;
            }
        });
    }
});
</script>