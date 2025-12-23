@extends('layouts.main')

@push('styles')
<style>
    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #0d6efd;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        display: none;
        margin: auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush


@section('content')

<div class="container my-4">

    <h3 class="mb-4">Keranjang Belanja</h3>

    {{-- Alerts --}}
    @foreach (['success','warning','error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg }}">{{ session($msg) }}</div>
        @endif
    @endforeach

    @if(count($items) > 0)
    <div class="row">

        {{-- CART --}}
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-warning">
                        <tr>
                            <th>Gambar</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>
                                <img src="{{ $item['product']->gambar ? asset($item['product']->gambar) : 'https://via.placeholder.com/80' }}"
                                     width="80" class="rounded">
                            </td>
                            <td>
                                <strong>{{ $item['product']->nama }}</strong><br>
                                <small class="text-muted">{{ $item['product']->deskripsi }}</small>
                            </td>
                            <td>Rp {{ number_format($item['price'],0,',','.') }}</td>
                            <td>
                                <form method="POST" action="{{ route('cart.update',$item['product_id']) }}" class="d-flex gap-2">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                           min="1" max="{{ $item['product']->stok }}"
                                           class="form-control form-control-sm" style="width:70px">
                                    <button class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td>Rp {{ number_format($item['subtotal'],0,',','.') }}</td>
                            <td>
                                <form method="POST" action="{{ route('cart.destroy',$item['product_id']) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header bg-warning fw-bold">Ringkasan Pesanan</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($total,0,',','.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkir</span>
                        <span id="shipping-cost">Rp 0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span id="total-price">Rp {{ number_format($total,0,',','.') }}</span>
                    </div>

                    <a href="{{ auth()->check() ? route('payment.index') : route('login') }}"
                       class="btn btn-warning w-100 mt-3">
                        {{ auth()->check() ? 'Lanjut ke Pembayaran' : 'Login untuk Checkout' }}
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ONGKIR --}}
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="mb-3">Hitung Ongkos Kirim</h5>

            <div class="row g-3">
                <div class="col-md-4">
                    <select id="province" class="form-select">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="city" class="form-select">
                        <option value="">Pilih Kota</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="district" class="form-select">
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" id="weight" class="form-control" placeholder="Berat (gram)">
                </div>
            </div>

            <div class="mt-3">
                @foreach(['jne','jnt','sicepat','anteraja','pos','tiki'] as $c)
                    <label class="me-3">
                        <input type="radio" name="courier" value="{{ $c }}"> {{ strtoupper($c) }}
                    </label>
                @endforeach
            </div>

            <button id="btn-ongkir" class="btn btn-primary mt-3">Hitung Ongkir</button>
            <div class="loader mt-3" id="loading-indicator"></div>

            <div id="results-ongkir" class="mt-3"></div>
        </div>
    </div>

    @else
        <div class="alert alert-info text-center">Keranjang kosong</div>
    @endif

</div>

@endsection


@push('scripts')
<script>
$(function () {

    const rupiah = n => new Intl.NumberFormat('id-ID').format(n);

    $('#province').change(function () {
        $('#city').html('<option>Loading...</option>');
        $.get(`/cities/${this.value}`, r => {
            $('#city').html('<option>Pilih Kota</option>');
            r.forEach(v => $('#city').append(`<option value="${v.id}">${v.name}</option>`));
        });
    });

    $('#city').change(function () {
        $('#district').html('<option>Loading...</option>');
        $.get(`/districts/${this.value}`, r => {
            $('#district').html('<option>Pilih Kecamatan</option>');
            r.forEach(v => $('#district').append(`<option value="${v.id}">${v.name}</option>`));
        });
    });

    $('#btn-ongkir').click(function () {

        let data = {
            _token: $('meta[name=csrf-token]').attr('content'),
            district_id: $('#district').val(),
            courier: $('input[name=courier]:checked').val(),
            weight: $('#weight').val()
        };

        if (!data.district_id || !data.courier || !data.weight) {
            alert('Lengkapi data');
            return;
        }

        $('#loading-indicator').show();

        $.post('/check-ongkir', data, res => {
            $('#results-ongkir').empty();
            let cost = res[0].cost;
            $('#shipping-cost').text('Rp ' + rupiah(cost));
            $('#total-price').text('Rp ' + rupiah(cost + {{ $total }}));

            res.forEach(v => {
                $('#results-ongkir').append(`
                    <div class="d-flex justify-content-between border rounded p-2 mb-2">
                        <span>${v.service} (${v.etd})</span>
                        <strong>Rp ${rupiah(v.cost)}</strong>
                    </div>
                `);
            });
        }).always(() => $('#loading-indicator').hide());
    });

});
</script>
@endpush

