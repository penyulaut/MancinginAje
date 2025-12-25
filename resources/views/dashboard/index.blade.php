@extends('layouts.main')

@section('content')
  <x-sidebar></x-sidebar>

  <!-- Main Content -->
  <div class="search-bar px-5" style="margin-left: 220px; padding-bottom: 120px; min-height: calc(100vh - 120px);">
    <nav class="navbar p-2 m-2">
      <span class="navbar-brand fw-bold text-light">Dashboard Admin</span>
      {{-- Tambah data dipindahkan ke tab Data Produk --}}
    </nav>



    @php $activeTab = $activeTab ?? request('tab', 'dashboard'); @endphp

    {{-- (Status Transaksi moved to Transactions tab) --}}

    {{-- Tabbed Content: Dashboard / Products / Transactions / Users / Reports --}}

    @if($activeTab === 'dashboard')
      <!-- Dashboard Overview -->
      <div class="row g-4 mb-4">
        <!-- Total Sales Card -->
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Sales</p>
                  <h3 class="fw-bold mb-0" style="color: #1e293b;">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                  <i class="fas fa-dollar-sign text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Products Card -->
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Products</p>
                  <h3 class="fw-bold mb-0" style="color: #1e293b;">{{ $totalProducts ?? 0 }}</h3>
                  <div class="mt-2">
                    <span class="badge bg-success me-1">{{ $activeProducts ?? 0 }} Active</span>
                    <span class="badge bg-secondary">{{ $inactiveProducts ?? 0 }} Inactive</span>
                  </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                  <i class="fas fa-box text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Categories Card -->
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Categories</p>
                  <h3 class="fw-bold mb-0" style="color: #1e293b;">{{ $totalCategories ?? 0 }}</h3>
                  <a href="{{ route('admin.categories.index') }}" class="mt-2 d-inline-block text-decoration-none" style="font-size: 0.8rem; color: #10b981;">Manage →</a>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                  <i class="fas fa-tags text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Orders Card -->
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Orders</p>
                  <h3 class="fw-bold mb-0" style="color: #1e293b;">{{ $totalTransactions ?? 0 }}</h3>
                  <div class="mt-2">
                    <span class="badge" style="background: #fbbf24; color: #78350f;">{{ $transactionsPending ?? 0 }} Pending</span>
                  </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                  <i class="fas fa-shopping-cart text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Recent Products -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #1e293b;">Recent Products</h5>
                <a href="{{ route('admin.dashboard.index', ['tab' => 'products']) }}" class="text-decoration-none" style="color: #10b981; font-size: 0.9rem;">View All →</a>
              </div>
            </div>
            <div class="card-body px-4">
              @if(isset($recentProducts) && $recentProducts->count() > 0)
                <div class="table-responsive">
                  <table class="table table-borderless mb-0">
                    <tbody>
                      @foreach($recentProducts as $product)
                        <tr>
                          <td class="ps-0" style="width: 50px;">
                            @if($product->gambar)
                              <img src="{{ asset($product->gambar) }}" alt="{{ $product->nama }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                              <div class="rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #f1f5f9;">
                                <i class="fas fa-image text-muted"></i>
                              </div>
                            @endif
                          </td>
                          <td>
                            <div class="fw-semibold" style="color: #1e293b;">{{ Str::limit($product->nama, 25) }}</div>
                            <small class="text-muted">{{ optional($product->category)->nama ?? '-' }}</small>
                          </td>
                          <td class="text-end pe-0">
                            <div class="fw-semibold" style="color: #10b981;">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
                            <small class="text-muted">Stock: {{ $product->stok }}</small>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <p class="text-muted mb-0 text-center py-3">Belum ada produk.</p>
              @endif
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #1e293b;">Recent Orders</h5>
                <a href="{{ route('admin.dashboard.index', ['tab' => 'transactions']) }}" class="text-decoration-none" style="color: #10b981; font-size: 0.9rem;">View All →</a>
              </div>
            </div>
            <div class="card-body px-4">
              @if(isset($recentOrders) && $recentOrders->count() > 0)
                <div class="table-responsive">
                  <table class="table table-borderless mb-0">
                    <tbody>
                      @foreach($recentOrders as $order)
                        <tr>
                          <td class="ps-0">
                            <div class="fw-semibold" style="color: #1e293b;">#{{ $order->id }}</div>
                            <small class="text-muted">{{ $order->created_at->format('d M Y') }}</small>
                          </td>
                          <td>
                            <div style="color: #64748b;">{{ $order->customer_name ?? optional($order->user)->name ?? 'Guest' }}</div>
                          </td>
                          <td class="text-end pe-0">
                            <div class="fw-semibold" style="color: #1e293b;">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</div>
                            @php
                              $status = $order->payment_status ?? $order->status;
                              $statusColor = match($status) {
                                'paid', 'completed' => 'background: #d1fae5; color: #065f46;',
                                'pending' => 'background: #fef3c7; color: #92400e;',
                                default => 'background: #fee2e2; color: #991b1b;'
                              };
                            @endphp
                            <span class="badge" style="{{ $statusColor }} font-size: 0.7rem;">{{ ucfirst($status) }}</span>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <p class="text-muted mb-0 text-center py-3">Belum ada pesanan.</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Alert -->
      @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
        <div class="card border-0 shadow-sm mt-4" style="border-radius: 15px; border-left: 4px solid #f59e0b !important;">
          <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle text-warning me-2"></i>
              <h5 class="fw-bold mb-0" style="color: #1e293b;">Low Stock Alert</h5>
              <span class="badge bg-warning text-dark ms-2">{{ $lowStockProducts->count() }} items</span>
            </div>
          </div>
          <div class="card-body px-4 pt-3">
            <div class="row">
              @foreach($lowStockProducts->take(4) as $product)
                <div class="col-md-3 mb-3">
                  <div class="d-flex align-items-center p-3 rounded" style="background: #fffbeb;">
                    @if($product->gambar)
                      <img src="{{ asset($product->gambar) }}" alt="{{ $product->nama }}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                    @else
                      <div class="rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #fef3c7;">
                        <i class="fas fa-image text-warning"></i>
                      </div>
                    @endif
                    <div>
                      <div class="fw-semibold" style="color: #1e293b; font-size: 0.9rem;">{{ Str::limit($product->nama, 15) }}</div>
                      <div class="text-danger fw-bold">{{ $product->stok }} left</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif

    @elseif($activeTab === 'products')
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
      <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-4 pb-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h5 class="fw-bold mb-1" style="color: #1e293b;">Data Transaksi</h5>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge" style="background: #fef3c7; color: #92400e; padding: 6px 12px;">{{ $transactionsPending ?? 0 }} Pending</span>
                <span class="badge" style="background: #d1fae5; color: #065f46; padding: 6px 12px;">{{ $transactionsPaid ?? 0 }} Paid</span>
                <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 6px 12px;">{{ $transactionsCancelled ?? 0 }} Cancelled</span>
              </div>
            </div>
            <a href="{{ route('admin.transactions.export', request()->only(['status', 'sort'])) }}" class="btn btn-success" style="border-radius: 10px;">
              <i class="fas fa-file-excel me-2"></i>Export XLSX
            </a>
          </div>
        </div>
        <div class="card-body pt-0 px-4">
          <form method="GET" class="row g-2 mb-4">
            <input type="hidden" name="tab" value="transactions">
            <div class="col-md-3">
              <label class="form-label small text-muted">Urutkan</label>
              <select name="sort" class="form-select" style="border-radius: 10px;">
                <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Tanggal (Terbaru)</option>
                <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Tanggal (Terlama)</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted">Status</label>
              <select name="status" class="form-select" style="border-radius: 10px;">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>
            <div class="col-md-3 align-self-end">
              <button class="btn btn-primary w-100" style="border-radius: 10px;">
                <i class="fas fa-filter me-2"></i>Filter
              </button>
            </div>
          </form>

          @if(isset($allOrders) && $allOrders && $allOrders->count())
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr style="background: #f8fafc;">
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">#</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">ORDER ID</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">TANGGAL</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">PELANGGAN</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">ITEMS</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">TOTAL</th>
                    <th class="py-3 px-3 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">STATUS</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($allOrders as $ord)
                    <tr>
                      <td class="py-3 px-3 border-0 align-middle">{{ $loop->iteration + (($allOrders->currentPage()-1) * $allOrders->perPage()) }}</td>
                      <td class="py-3 px-3 border-0 align-middle">
                        <span class="fw-semibold" style="color: #1e293b;">#{{ $ord->id }}</span>
                      </td>
                      <td class="py-3 px-3 border-0 align-middle">
                        <div style="color: #1e293b;">{{ $ord->created_at->format('d M Y') }}</div>
                        <small class="text-muted">{{ $ord->created_at->format('H:i') }}</small>
                      </td>
                      <td class="py-3 px-3 border-0 align-middle">
                        <div class="fw-semibold" style="color: #1e293b;">{{ $ord->customer_name ?? optional($ord->user)->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ optional($ord->user)->email ?? '-' }}</small>
                      </td>
                      <td class="py-3 px-3 border-0 align-middle">
                        @foreach($ord->items->take(2) as $item)
                          <div class="small">{{ Str::limit($item->product?->nama ?? 'Produk', 20) }} <span class="text-muted">×{{ $item->quantity }}</span></div>
                        @endforeach
                        @if($ord->items->count() > 2)
                          <small class="text-muted">+{{ $ord->items->count() - 2 }} lainnya</small>
                        @endif
                      </td>
                      <td class="py-3 px-3 border-0 align-middle">
                        <span class="fw-bold" style="color: #10b981;">Rp {{ number_format($ord->total_harga, 0, ',', '.') }}</span>
                      </td>
                      <td class="py-3 px-3 border-0 align-middle">
                        @php
                          $status = $ord->payment_status ?? $ord->status;
                          $statusStyle = match($status) {
                            'paid', 'completed' => 'background: #d1fae5; color: #065f46;',
                            'pending' => 'background: #fef3c7; color: #92400e;',
                            default => 'background: #fee2e2; color: #991b1b;'
                          };
                        @endphp
                        <span class="badge" style="{{ $statusStyle }} padding: 6px 12px; border-radius: 8px;">{{ ucfirst($status) }}</span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
              <div class="text-muted small">
                Menampilkan {{ $allOrders->firstItem() ?? 0 }} - {{ $allOrders->lastItem() ?? 0 }} dari {{ $allOrders->total() }} transaksi
              </div>
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  @if($allOrders->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">← Prev</span></li>
                  @else
                    <li class="page-item"><a class="page-link" href="{{ $allOrders->previousPageUrl() }}">← Prev</a></li>
                  @endif
                  
                  @foreach($allOrders->getUrlRange(1, $allOrders->lastPage()) as $page => $url)
                    @if($page == $allOrders->currentPage())
                      <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                      <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                  @endforeach
                  
                  @if($allOrders->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $allOrders->nextPageUrl() }}">Next →</a></li>
                  @else
                    <li class="page-item disabled"><span class="page-link">Next →</span></li>
                  @endif
                </ul>
              </nav>
            </div>
          @else
            <div class="text-center py-5 text-muted">
              <i class="fas fa-receipt fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
              <p class="mb-0">Belum ada transaksi.</p>
            </div>
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