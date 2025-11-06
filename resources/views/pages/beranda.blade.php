@extends('layouts.main')

@section('content')
  {{-- Navbar --}}
  <x-navbar/>

  {{-- Home --}}
  <section class="hero text-light text-center py-5">      
    <div class="container d-flex flex-column justify-content-center align-items-center h-100">
      <h1 class="display-3 fw-bold mb-3">Welcome to <span class="text-warning">Kenangan Senja</span></h1>
      <p class="lead mb-4">Rasakan kehangatan setiap cangkir dan suasana yang penuh cerita</p>
      <a href="#menu" class="btn btn-warning btn-lg px-4 rounded-pill shadow">Lihat Menu</a>
    </div>
  </section>

  {{-- Aboout --}}
  <section class="about bg-light pt-5" id="about">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <img src="{{asset ('images/aboutimg.jpg')}}" class="img-fluid rounded" alt="About Kenangan Senja">
        </div>
        <div class="col-md-6">
          <h2 class="fw-bold mb-3">Tentang Kenangan Senja</h2>
          <p class="mb-4">Kenangan Senja adalah kafe yang mengusung konsep hangout spot dengan suasana yang nyaman dan instagramable. Kami menyajikan berbagai pilihan kopi spesial, minuman seggar, serta camilan lezat yang cocok untuk menemani waktu santai Anda.</p>
          <a href="#menu" class="btn btn-warning rounded-pill px-4 shadow">Lihat Menu Kami</a>
        </div>
      </div>
    </div>
  </section>

  <section class="menu pt-5" id="menu">
    <div class="container my-5">
      <div class="row">
        <div class="col text-center mb-4">
          <h2 class="fw-bold">Top Menu Terlaris</h2>
          <p class="text-muted">Nikmati pilihan terbaik dari kami</p>
          <a href="/beranda/orders" class="btn btn-warning rounded-pill px-4">Lihat Semua</a>
        </div>
      </div>
      <div class="row">
        {{-- Card Menu 1 --}}
        @foreach ($products as $item)
          <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
              <img src="{{ asset($item->gambar) }}" class="card-img-top" style="height: 20rem; object-fit: cover;" alt="Product Image">
              <div class="card-body text-center">
                <h5 class="card-title fw-bold">{{$item->nama}}</h5>
                <p class="card-text text-muted">{{$item->deskripsi}}</p>
                <p class="card-text fw-bold">Rp {{$item->harga}}</p>
              </div>
            </div>
          </div>              
        @endforeach         
      </div>
      
    </div>                
  </section>

  {{-- Contact --}}
  <section id="contact" class="contact">
    <div class="container py-5">
      <div class="row">
        <div class="col text-center mb-4">
          <h2 class="fw-bold">Hubungi Kami</h2>
          <p class="text-muted">Kami senang mendengar dari Anda!</p>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form>
            <div class="mb-3">
              <label for="name" class="form-label">Nama</label>
              <input type="text" class="form-control" id="name" placeholder="Masukkan nama Anda">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" placeholder="Masukkan email Anda">
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">Pesan</label>
              <textarea class="form-control" id="message" rows="4" placeholder="Tulis pesan Anda di sini"></textarea>
            </div>
            <button type="submit" class="btn btn-warning rounded-pill px-4">Kirim Pesan</button>
          </form>
        </div>
      </div>
    </div>
  </section>
@endsection