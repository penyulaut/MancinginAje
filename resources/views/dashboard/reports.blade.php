@extends('layouts.main')

@section('content')
  <x-sidebar/>

  <div class="content">
    <div class="container mt-5 pt-4">
    <h3>Laporan Belanja</h3>

    <form method="GET" class="row g-3 my-3">
      <div class="col-md-3">
        <label class="form-label">Periode</label>
        <select name="period" id="period" class="form-select">
          <option value="daily" {{ $period==='daily' ? 'selected' : '' }}>Harian</option>
          <option value="monthly" {{ $period==='monthly' ? 'selected' : '' }}>Bulanan</option>
          <option value="yearly" {{ $period==='yearly' ? 'selected' : '' }}>Tahunan</option>
        </select>
      </div>

      <div class="col-md-3" id="date-input">
        <label class="form-label">Tanggal / Bulan / Tahun</label>
        <input type="date" class="form-control" name="date" value="{{ old('date', $dateInput ?? now()->toDateString()) }}">
      </div>

      <div class="col-md-3">
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-select">
          <option value="">Semua Kategori</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ (string)($categoryId ?? '') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3 align-self-end">
        <button class="btn btn-primary">Tampilkan</button>
      </div>
    </form>

    <div class="card mb-3">
      <div class="card-body">
        <h5>Total Penjualan: Rp {{ number_format($totalSales, 0, ',', '.') }}</h5>
        <p>Total Orders: {{ $totalOrders }}</p>
        <p>Periode: {{ $startDate ?? ($start ?? '') }} — {{ $endDate ?? ($end ?? '') }}</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Order ID</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Items (produk / qty)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orders as $order)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $order->id }}</td>
              <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
              <td>{{ $order->customer_name ?? optional($order->user)->name ?? '-' }}</td>
              <td>Rp {{ number_format($order->total_harga,0,',','.') }}</td>
              <td>
                @foreach($order->items as $it)
                  @php $p = $it->product; @endphp
                  <div>{{ optional($p)->nama ?? 'Produk dihapus' }} &times; {{ $it->quantity }}</div>
                @endforeach
              </td>
            </tr>
          @endforeach
          @if($orders->isEmpty())
            <tr><td colspan="6" class="text-center">Tidak ada data untuk periode ini.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const period = document.getElementById('period');
      const dateInput = document.getElementById('date-input');

      function updateDateInput() {
        if (period.value === 'daily') {
          dateInput.innerHTML = '<label class="form-label">Tanggal</label><input type="date" class="form-control" name="date" value="{{ $dateInput ?? now()->toDateString() }}">';
        } else if (period.value === 'monthly') {
          dateInput.innerHTML = '<label class="form-label">Bulan</label><input type="month" class="form-control" name="date" value="{{ $dateInput ? substr($dateInput,0,7) : now()->format("Y-m") }}">';
        } else {
          dateInput.innerHTML = '<label class="form-label">Tahun</label><input type="number" class="form-control" name="date" value="{{ $dateInput ? substr($dateInput,0,4) : now()->format("Y") }}">';
        }
      }

      period.addEventListener('change', updateDateInput);
      updateDateInput();
    });
  </script>

@endsection
