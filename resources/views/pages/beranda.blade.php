@extends('layouts.main')

@section('content')
  
  {{-- Hero Section Modern --}}
  <section class="hero-modern d-flex align-items-center">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 80px; right: 20px; z-index: 9999;" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 text-white">
          <h1 class="display-3 fw-bold mb-4 animate-fade-in">
            Peralatan Pancing <br>
            <span class="text-gradient">Berkualitas Tinggi</span>
          </h1>
          <p class="lead mb-4 text-white-50">Temukan berbagai pilihan alat pancing profesional untuk petualangan memancing terbaik Anda</p>
          <div class="d-flex gap-3 flex-wrap">
            <a href="#products" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg">
              <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
            </a>
            <a href="#about" class="btn btn-outline-light btn-lg px-5 rounded-pill">
              Pelajari Lebih Lanjut
            </a>
          </div>
        </div>
        <div class="col-lg-6 d-none d-lg-block">
          <div class="hero-image-container">
            <img src="{{asset('images/mancing.jpg')}}" class="img-fluid rounded-4 shadow-lg" alt="Fishing Equipment">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Features Section --}}
  <section class="features-section py-5 bg-light">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="feature-card text-center p-4">
            <div class="feature-icon mb-3">
              <i class="fas fa-shipping-fast fa-3x text-primary"></i>
            </div>
            <h5 class="fw-bold">Pengiriman Cepat</h5>
            <p class="text-muted mb-0">Gratis ongkir ke seluruh Indonesia</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="feature-card text-center p-4">
            <div class="feature-icon mb-3">
              <i class="fas fa-shield-alt fa-3x text-success"></i>
            </div>
            <h5 class="fw-bold">Garansi Resmi</h5>
            <p class="text-muted mb-0">Produk bergaransi 100% original</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="feature-card text-center p-4">
            <div class="feature-icon mb-3">
              <i class="fas fa-headset fa-3x text-info"></i>
            </div>
            <h5 class="fw-bold">CS 24/7</h5>
            <p class="text-muted mb-0">Siap membantu kapan saja</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="feature-card text-center p-4">
            <div class="feature-icon mb-3">
              <i class="fas fa-lock fa-3x text-warning"></i>
            </div>
            <h5 class="fw-bold">Pembayaran Aman</h5>
            <p class="text-muted mb-0">Transaksi dijamin aman</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Products Section --}}
  <section class="products-section py-5" id="products">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold mb-3">Produk Terlaris</h2>
        <p class="text-muted fs-5">Pilihan favorit para pemancing profesional</p>
      </div>
      
      <div class="row g-4">
        @foreach ($products as $item)
          <div class="col-lg-3 col-md-6">
            <div class="product-card-modern h-100" style="position:relative;">
              <div class="product-image-wrapper">
                <img src="{{ asset($item->gambar) }}" class="product-image" alt="{{ $item->nama }}" loading="lazy">
              </div>
              <div class="product-body p-3">
                <h5 class="product-title fw-bold mb-2">{{ $item->nama }}</h5>
                <a href="{{ route('products.show', $item->id) }}" class="stretched-link" aria-label="Lihat detail {{ $item->nama }}"></a>
                <p class="product-description text-muted small mb-3">{{ Str::limit($item->deskripsi, 50) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="product-price fw-bold text-primary fs-5">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                  <span class="badge bg-success-soft text-success">Tersedia</span>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      
      <div class="text-center mt-5">
        <a href="/beranda/orders" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
          <i class="fas fa-th-large me-2"></i>Lihat Semua Produk
        </a>
      </div>
    </div>                
  </section>

  {{-- Contact Section Modern --}}
  <section id="contact" class="contact-section py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold mb-3">Hubungi Kami</h2>
        <p class="text-muted fs-5">Ada pertanyaan? Kami siap membantu Anda</p>
      </div>
      
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="contact-info-card p-4 h-100">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-box bg-primary text-white rounded-3 p-3 me-3">
                <i class="fas fa-phone-alt fa-lg"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">Telepon</h5>
                <p class="text-muted mb-0">+62 812-3456-7890</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="contact-info-card p-4 h-100">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-box bg-success text-white rounded-3 p-3 me-3">
                <i class="fas fa-envelope fa-lg"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">Email</h5>
                <p class="text-muted mb-0">info@mancinginaje.com</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="contact-info-card p-4 h-100">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-box bg-info text-white rounded-3 p-3 me-3">
                <i class="fas fa-map-marker-alt fa-lg"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">Alamat</h5>
                <p class="text-muted mb-0">Jakarta, Indonesia</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
          <div class="contact-form-card p-5">
            <form>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                  <input type="text" class="form-control form-control-lg" id="name" placeholder="Masukkan nama Anda">
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label fw-semibold">Email</label>
                  <input type="email" class="form-control form-control-lg" id="email" placeholder="email@example.com">
                </div>
                <div class="col-12">
                  <label for="subject" class="form-label fw-semibold">Subjek</label>
                  <input type="text" class="form-control form-control-lg" id="subject" placeholder="Subjek pesan">
                </div>
                <div class="col-12">
                  <label for="message" class="form-label fw-semibold">Pesan</label>
                  <textarea class="form-control" id="message" rows="5" placeholder="Tulis pesan Anda..."></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection