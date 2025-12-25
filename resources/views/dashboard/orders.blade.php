@extends('layouts.main')

@section('content')
  <x-sidebar></x-sidebar>

  <div class="admin-content" style="margin-left: 250px; padding: 30px; min-height: calc(100vh - 120px); background: #f8fafc;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 fw-bold" style="color: #1e293b;">Orders Pending</h2>
        <p class="text-muted mb-0">Pesanan yang sedang berlangsung / menunggu pembayaran</p>
      </div>
      <div>
        <span class="badge bg-warning text-dark p-2 fs-6">{{ $orders->count() }} Pending</span>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr style="background: #f1f5f9;">
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">#</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">USER</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">ITEMS</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">TOTAL</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">STATUS</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">TANGGAL</th>
                <th class="py-3 px-4 border-0" style="color: #64748b; font-weight: 600; font-size: 0.8rem;">AKSI</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders as $order)
                <tr>
                  <td class="py-3 px-4 border-0 align-middle">
                    <span class="fw-semibold" style="color: #1e293b;">#{{ $order->id }}</span>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                      </div>
                      <div>
                        <div class="fw-semibold" style="color: #1e293b;">{{ $order->customer_name ?? $order->user?->name ?? 'Guest' }}</div>
                        <small class="text-muted">{{ $order->user?->email ?? '-' }}</small>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    @foreach($order->items as $it)
                      <div class="mb-1">
                        <span style="color: #1e293b;">{{ $it->product?->nama ?? 'Produk terhapus' }}</span>
                        <span class="badge bg-light text-dark">× {{ $it->quantity }}</span>
                      </div>
                    @endforeach
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    <span class="fw-bold" style="color: #10b981;">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    @php
                      $status = $order->payment_status ?? $order->status;
                      $statusStyle = match($status) {
                        'paid', 'completed' => 'background: #d1fae5; color: #065f46;',
                        'pending' => 'background: #fef3c7; color: #92400e;',
                        default => 'background: #fee2e2; color: #991b1b;'
                      };
                    @endphp
                    <span class="badge" style="{{ $statusStyle }} padding: 6px 12px; border-radius: 8px;">{{ ucfirst($status) }}</span>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    <div style="color: #64748b;">{{ $order->created_at->format('d M Y') }}</div>
                    <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                  </td>
                  <td class="py-3 px-4 border-0 align-middle">
                    @php $orderStatus = $order->payment_status ?? $order->status; @endphp
                    @if($orderStatus === 'pending')
                      <form action="{{ route('admin.orders.accept', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ACC pesanan ini?');">
                        @csrf
                        <button class="btn btn-sm btn-success" style="border-radius: 8px;">
                          <i class="fas fa-check me-1"></i>ACC
                        </button>
                      </form>
                      <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.');">
                        @csrf
                        <button class="btn btn-sm btn-danger" style="border-radius: 8px;">
                          <i class="fas fa-times me-1"></i>Batal
                        </button>
                      </form>
                    @elseif($orderStatus === 'paid' || $orderStatus === 'completed')
                      <span class="text-success"><i class="fas fa-check-circle me-1"></i>Lunas</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                    Belum ada pesanan.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

