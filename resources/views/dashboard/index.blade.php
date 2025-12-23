@extends('layouts.main')

@section('content')
  <x-sidebar></x-sidebar>

  <!-- Main Content -->
  <div class="search-bar px-5" style="margin-left: 220px; padding-bottom: 120px; min-height: calc(100vh - 120px);">
    <nav class="navbar p-2 m-2">
      <span class="navbar-brand fw-bold text-light">Dashboard Admin</span>
      {{-- Tambah data dipindahkan ke tab Data Produk --}}
    </nav>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted">Produk</div>
              <h4 class="mb-0">{{ $totalProducts ?? $products->count() }}</h4>
            </div>
            <div><i class="fas fa-box fa-2x text-primary"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted">Transaksi</div>
              <h4 class="mb-0">{{ $totalTransactions ?? 0 }}</h4>
            </div>
            <div><i class="fas fa-receipt fa-2x text-success"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted">Pengguna</div>
              <h4 class="mb-0">{{ $totalUsers ?? 0 }}</h4>
            </div>
            <div><i class="fas fa-users fa-2x text-warning"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted">Pendapatan (Lunas)</div>
              <h4 class="mb-0">Rp {{ number_format($totalRevenue ?? 0,0,',','.') }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    @php $activeTab = $activeTab ?? request('tab', 'products'); @endphp

    {{-- (Status Transaksi moved to Transactions tab) --}}

    {{-- Tabbed Content: Products / Transactions / Users / Reports --}}

    @if($activeTab === 'products')
      <div class="table-container">
        @php $createRoute = (auth()->user() && auth()->user()->role === 'admin') ? route('admin.dashboard.create') : route('dashboard.create'); @endphp
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Data Produk</h5>
          <a href="{{ $createRoute }}" class="btn btn-warning">Tambah data</a>
        </div>

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
              <td>
                {{ $item->stok }}
                @if($item->stok < 10 && $item->stok > 0)
                  <div class="badge bg-warning text-dark ms-2">Stok Terbatas</div>
                @elseif($item->stok == 0)
                  <div class="badge bg-danger ms-2">Habis</div>
                @endif
              </td>
              <td>{{ optional($item->category)->nama ?? '-' }}</td>
              <td>{{ optional($item->seller)->name ?? '—' }}</td>
              <td>
                @php
                  if (auth()->user() && auth()->user()->role === 'admin') {
                    $editRoute = route('admin.dashboard.edit', ['id'=>$item->id]);
                    $destroyRoute = route('admin.dashboard.destroy', ['id'=>$item->id]);
                  } else {
                    $editRoute = route('dashboard.edit', ['id'=>$item->id]);
                    $destroyRoute = route('dashboard.destroy', ['id'=>$item->id]);
                  }
                @endphp

                <a href="{{ $editRoute }}" class="btn btn-sm btn-primary text-light">Edit</a>

                <form method="POST" action="{{ $destroyRoute }}" style="display:inline-block" onsubmit="return confirm('Hapus produk ini?');">
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

    @elseif($activeTab === 'transactions')
      <div class="card mt-4">
        <div class="card-body">
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Transaksi</h5>
            <div class="d-flex gap-2">
              <div class="badge bg-secondary p-2">Pending: {{ $transactionsPending ?? 0 }}</div>
              <div class="badge bg-success p-2">Paid: {{ $transactionsPaid ?? 0 }}</div>
              <div class="badge bg-danger p-2">Cancelled: {{ $transactionsCancelled ?? 0 }}</div>
            </div>
          </div>
          <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="tab" value="transactions">
            <div class="col-md-3">
              <select name="sort" class="form-select">
                <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Tanggal (Terbaru)</option>
                <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Tanggal (Terlama)</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>
            <div class="col-md-3 align-self-end">
              <button class="btn btn-primary">Apply</button>
            </div>
          </form>

          @if(isset($allOrders) && $allOrders && $allOrders->count())
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Order</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($allOrders as $ord)
                    <tr>
                      <td>{{ $loop->iteration + (($allOrders->currentPage()-1) * $allOrders->perPage()) }}</td>
                      <td>{{ $ord->id }}</td>
                      <td>{{ $ord->created_at->format('Y-m-d H:i') }}</td>
                      <td>{{ $ord->customer_name ?? optional($ord->user)->name ?? '-' }}</td>
                      <td>Rp {{ number_format($ord->total_harga,0,',','.') }}</td>
                      <td>{{ ucfirst($ord->payment_status ?? $ord->status) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              {{ $allOrders->links() }}
            </div>
          @elseif(isset($recentTransactions) && $recentTransactions->isNotEmpty())
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Order</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentTransactions as $ord)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $ord->id }}</td>
                      <td>{{ $ord->created_at->format('Y-m-d H:i') }}</td>
                      <td>{{ $ord->customer_name ?? optional($ord->user)->name ?? '-' }}</td>
                      <td>Rp {{ number_format($ord->total_harga,0,',','.') }}</td>
                      <td>{{ ucfirst($ord->payment_status ?? $ord->status) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="mb-0">Belum ada transaksi.</p>
          @endif
        </div>
      </div>

    @elseif($activeTab === 'users')
      <div class="card mt-4">
        <div class="card-body">
          <h5>Pengguna Terbaru</h5>
          @if(isset($recentUsers) && $recentUsers->isNotEmpty())
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentUsers as $u)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $u->name }}</td>
                      <td>{{ $u->email }}</td>
                      <td>{{ $u->role ?? 'customer' }}</td>
                      <td>{{ $u->created_at->format('Y-m-d') }}</td>
                      <td>
                        @if(auth()->check() && auth()->user()->role === 'admin')
                          @if(auth()->id() != $u->id)
                            <form method="POST" action="{{ route('admin.users.destroy', ['id' => $u->id]) }}" onsubmit="return confirm('Hapus akun ini beserta data terkait?');">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-sm btn-danger">Hapus Akun</button>
                            </form>
                          @else
                            <span class="text-muted">(Anda)</span>
                          @endif
                        @else
                          -
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="mb-0">Belum ada pengguna baru.</p>
          @endif
        </div>
      </div>

    @elseif($activeTab === 'reports')
      <div class="card mt-4">
        <div class="card-body">
          <h5>Laporan</h5>
          <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2">
            <div class="col-md-3">
              <label class="form-label">Tanggal</label>
              <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Periode</label>
              <select name="period" class="form-select">
                <option value="monthly">Bulanan</option>
                <option value="daily">Harian</option>
                <option value="yearly">Tahunan</option>
              </select>
            </div>
            <div class="col-md-3 align-self-end">
              <button class="btn btn-primary">Tampilkan</button>
            </div>
          </form>
        </div>
      </div>

    @endif

  </div>
@endsection