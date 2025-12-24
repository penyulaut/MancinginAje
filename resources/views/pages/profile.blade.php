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
                <div class="mb-3">
                  <h6>Alamat Tersimpan</h6>
                  @if(isset($addresses) && $addresses->isNotEmpty())
                    <ul class="list-group mb-3">
                      @foreach($addresses as $a)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                          <div>
                            <strong>{{ $a->label ?? 'Alamat' }}</strong>
                            <div class="small text-muted">{{ $a->address_line }}</div>
                            <div class="small text-muted">{{ $a->district_name }}, {{ $a->city_name }}, {{ $a->province_name }}</div>
                          </div>
                          <div class="text-end">
                            @if($a->is_default)
                              <span class="badge bg-success">Default</span>
                            @endif
                            <form action="{{ route('profile.address.delete', $a->id) }}" method="POST" style="display:inline-block; margin-left:8px;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                          </div>
                        </li>
                      @endforeach
                    </ul>
                  @else
                    <p class="text-muted">Belum ada alamat tersimpan.</p>
                  @endif
                </div>

                <form action="{{ route('profile.address.update') }}" method="POST" id="form-add-address">
                  @csrf
                  <h6>Tambah Alamat Baru</h6>
                  <div class="row g-2">
                    <div class="col-md-4">
                      <label class="form-label">Label</label>
                      <input name="label" class="form-control" placeholder="Rumah / Kantor">
                    </div>
                    <div class="col-md-8">
                      <label class="form-label">Alamat</label>
                      <input name="address_line" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Provinsi</label>
                      <select id="province-select" name="province_id" class="form-select">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $p)
                          <option value="{{ $p['id'] }}" data-name="{{ $p['name'] }}">{{ $p['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Kota</label>
                      <select id="city-select" name="city_id" class="form-select"><option>Pilih Kota</option></select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Kecamatan</label>
                      <select id="district-select" name="district_id" class="form-select"><option>Pilih Kecamatan</option></select>
                    </div>
                    <!-- hidden inputs to persist readable names so we can use them later for display / ongkir -->
                    <input type="hidden" name="province_name" id="province-name-input" value="">
                    <input type="hidden" name="city_name" id="city-name-input" value="">
                    <input type="hidden" name="district_name" id="district-name-input" value="">
                    <div class="col-md-4">
                      <label class="form-label">Kode Pos</label>
                      <input name="postal_code" class="form-control">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                        <label class="form-check-label small" for="is_default">Jadikan default</label>
                      </div>
                    </div>
                  </div>
                  <div class="mt-3">
                    <button class="btn btn-primary">Tambah Alamat</button>
                  </div>
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

  @push('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    function fetchCities(provinceId) {
      const citySel = document.getElementById('city-select');
      const distSel = document.getElementById('district-select');
      if (!provinceId) { citySel.innerHTML = '<option>Pilih Kota</option>'; return; }
      citySel.innerHTML = '<option>Loading...</option>';
      fetch(`/cities/${provinceId}`).then(r => r.json()).then(data => {
        citySel.innerHTML = '<option value="">Pilih Kota</option>';
        data.forEach(v => citySel.insertAdjacentHTML('beforeend', `<option value="${v.id}" data-name="${v.name}">${v.name}</option>`));
      }).catch(() => citySel.innerHTML = '<option>Pilih Kota</option>');
      distSel.innerHTML = '<option>Pilih Kecamatan</option>';
    }

    function fetchDistricts(cityId) {
      const distSel = document.getElementById('district-select');
      if (!cityId) { distSel.innerHTML = '<option>Pilih Kecamatan</option>'; return; }
      distSel.innerHTML = '<option>Loading...</option>';
      fetch(`/districts/${cityId}`).then(r => r.json()).then(data => {
        distSel.innerHTML = '<option value="">Pilih Kecamatan</option>';
        data.forEach(v => distSel.insertAdjacentHTML('beforeend', `<option value="${v.id}" data-name="${v.name}">${v.name}</option>`));
      }).catch(() => distSel.innerHTML = '<option>Pilih Kecamatan</option>');
    }

    const province = document.getElementById('province-select');
    const city = document.getElementById('city-select');
    const district = document.getElementById('district-select');

    const provinceNameInput = document.getElementById('province-name-input');
    const cityNameInput = document.getElementById('city-name-input');
    const districtNameInput = document.getElementById('district-name-input');

    if (province) province.addEventListener('change', function () { 
      const id = this.value; 
      const sel = this.options[this.selectedIndex];
      if (sel && sel.dataset) provinceNameInput.value = sel.dataset.name || '';
      fetchCities(id); 
    });

    if (city) city.addEventListener('change', function () { 
      const sel = this.options[this.selectedIndex];
      cityNameInput.value = sel && sel.dataset ? (sel.dataset.name || '') : '';
      fetchDistricts(this.value); 
    });

    if (district) district.addEventListener('change', function () { 
      const sel = this.options[this.selectedIndex];
      districtNameInput.value = sel && sel.dataset ? (sel.dataset.name || '') : '';
    });

    // ensure hidden name inputs populated before submitting the form (in case user doesn't change selects after load)
    const addAddressForm = document.getElementById('form-add-address');
    if (addAddressForm) {
      addAddressForm.addEventListener('submit', function () {
        const psel = province && province.options[province.selectedIndex];
        const csel = city && city.options[city.selectedIndex];
        const dsel = district && district.options[district.selectedIndex];
        if (psel && psel.dataset) provinceNameInput.value = psel.dataset.name || '';
        if (csel && csel.dataset) cityNameInput.value = csel.dataset.name || '';
        if (dsel && dsel.dataset) districtNameInput.value = dsel.dataset.name || '';
      });
    }
  });
  </script>
  @endpush
