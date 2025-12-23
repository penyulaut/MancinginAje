@extends('layouts.main')

@section('content')
  <div class="container" style="margin-top:100px;">
    <div class="row">
      <div class="col-lg-8">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
          <div class="card-body">
            <h4 class="fw-bold mb-3">Profil Saya</h4>

            <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-profile" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profil</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-address" data-bs-toggle="tab" data-bs-target="#address" type="button">Alamat</button>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane fade show active" id="profile">
                <form action="{{ route('profile.update') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                  </div>
                  <button class="btn btn-primary">Simpan</button>
                </form>
              </div>

              <div class="tab-pane fade" id="address">
                <form action="{{ route('profile.address.update') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label">Alamat Pengiriman</label>
                    <textarea name="address" class="form-control" rows="4">{{ old('address', $user->address) }}</textarea>
                  </div>
                  <button class="btn btn-primary">Simpan Alamat</button>
                </form>
              </div>

              <div class="tab-pane fade" id="history">
                @if($orders->isEmpty())
                  <p class="text-muted">Belum ada pesanan.</p>
                @else
                  <ul class="list-group">
                    @foreach($orders as $order)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <div class="fw-bold">Order #{{ $order->id }}</div>
                          <small class="text-muted">{{ $order->created_at->format('d M Y') }} — Rp {{ number_format($order->total_harga ?? $order->total_price ?? 0,0,',','.') }}</small>
                        </div>
                        <div>
                          <span class="badge bg-{{ $order->status == 'paid' ? 'success' : 'secondary' }}">{{ $order->status }}</span>
                          <a href="/beranda/yourorders" class="btn btn-sm btn-outline-primary ms-2">Lihat</a>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-body text-center">
            <div class="mb-3">
              <i class="fas fa-user-circle fa-3x text-primary"></i>
            </div>
            <h5 class="fw-bold">{{ $user->name }}</h5>
            <p class="text-muted mb-1">{{ $user->email }}</p>
            <p class="text-muted small">{{ $user->phone ?? '-' }}</p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h6 class="fw-bold">Aksi Cepat</h6>
            <a href="{{ route('profile.history') }}" class="d-block mb-2">Lihat Semua Riwayat</a>
            <a href="/beranda/cart" class="d-block">Lihat Keranjang</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
