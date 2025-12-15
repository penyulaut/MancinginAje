@extends('layouts.main')

@section('content')
  <div class="container" style="margin-top:100px;">
    <div class="row">
      <div class="col-12">
        <h3 class="fw-bold mb-4">Riwayat Belanja</h3>

        @if($orders->isEmpty())
          <div class="alert alert-info">Belum ada riwayat belanja.</div>
        @else
          <div class="list-group">
            @foreach($orders as $order)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-bold">Order #{{ $order->id }}</div>
                  <small class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</small>
                  <div class="text-muted">Total: Rp {{ number_format($order->total_harga ?? $order->total_price ?? 0,0,',','.') }}</div>
                </div>
                <div>
                  <span class="badge bg-{{ $order->status == 'paid' ? 'success' : 'secondary' }} me-2">{{ $order->status }}</span>
                  <a href="/beranda/yourorders" class="btn btn-sm btn-outline-primary">Detail</a>
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-4">
            {{ $orders->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
