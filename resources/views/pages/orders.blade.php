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
    <div class="container">
        <ul class="nav nav-pills mb-3 justify-content-center custom-tabs" id="pills-tab" role="tablist">
          <li class="nav-item" role="all-products">
              <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab" aria-controls="pills-all" aria-selected="true">All Products</button>
          </li>
          <li class="nav-item " role="presentation">
              <button class="nav-link" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="false">Makanan</button>
          </li>
          <li class="nav-item" role="presentation">
              <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Minuman</button>
          </li>
          <li class="nav-item" role="presentation">
              <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Snack</button>
          </li>
      </ul>

      @if($products->isEmpty())
        <p class="text-center mt-3 fs-5">Produk tidak ditemukan 😢</p>
      @endif   

      <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">
          <div class="row">
            {{-- Card Menu 1 --}}
            @foreach ($products as $item)            
              <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                  <img src="{{ asset($item->gambar) }}" class="card-img-top" style="height: 20rem; object-fit: cover;" alt="Product Image">
                  <div class="card-body text-center">
                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                    {{-- <p class="card-text text-muted">{{ $item->deskripsi }}</p> --}}
                    <p class="card-text fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }} <span class="text-muted">Stok: {{ $item->stok }}</span></p>
                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-warning rounded-pill px-4">Add</a>
                  </div>
                </div>
              </div>   
            @endforeach                 
          </div>         
     
        </div>


        <div class="tab-pane fade show" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
          <div class="row">
            {{-- Card Menu 1 --}}
            @foreach ($makanan as $item)
              <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                  <img src="{{ asset($item->gambar) }}" class="card-img-top" style="height: 20rem; object-fit: cover;" alt="Product Image">
                  <div class="card-body text-center">
                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                    {{-- <p class="card-text text-muted">{{ $item->deskripsi }}</p> --}}
                    <p class="card-text fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }} <span class="text-muted">Stok: {{ $item->stok }}</span></p>
                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-warning rounded-pill px-4">Add</a>
                  </div>
                </div>
              </div>   
            @endforeach        
          </div>     
        </div>

        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
          <div class="row">
            {{-- Card Menu 1 --}}
            @foreach ($minuman as $item)
              <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                  <img src="{{ asset($item->gambar) }}" class="card-img-top" style="height: 20rem; object-fit: cover;" alt="Product Image">
                  <div class="card-body text-center">
                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                    {{-- <p class="card-text text-muted">{{ $item->deskripsi }}</p> --}}
                    <p class="card-text fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }} <span class="text-muted">Stok: {{ $item->stok }}</span></p>
                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-warning rounded-pill px-4">Add</a>
                  </div>
                </div>
              </div>   
            @endforeach        
          </div>      
        </div>
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
          <div class="row">
            {{-- Card Menu 1 --}}
            @foreach ($snack as $item)
              <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                  <img src="{{ asset($item->gambar) }}" class="card-img-top" style="height: 20rem; object-fit: cover;" alt="Product Image">
                  <div class="card-body text-center">
                    <h5 class="card-title fw-bold">{{ $item->nama }}</h5>
                    {{-- <p class="card-text text-muted">{{ $item->deskripsi }}</p> --}}
                    <p class="card-text fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }} <span class="text-muted">Stok: {{ $item->stok }}</span></p>
                    <a href="{{ route('products.show', $item->id) }}" class="btn btn-warning rounded-pill px-4">Add</a>
                  </div>
                </div>
              </div>   
            @endforeach        
          </div>      
        </div>
      </div>               

    </div>
    {{-- Tab Orders End --}}
   
    </div>
@endsection