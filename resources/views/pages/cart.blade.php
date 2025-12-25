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
                    @php
                        $shippingCostInitial = isset($shipping) ? (float)($shipping['cost'] ?? 0) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkir</span>
                        <span id="shipping-cost">Rp {{ number_format($shippingCostInitial,0,',','.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span id="total-price">Rp {{ number_format($total + $shippingCostInitial,0,',','.') }}</span>
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
                @if(auth()->check() && isset($addresses) && count($addresses) > 0)
                <div class="col-12 mb-2">
                    <label class="form-label">Pilih Alamat (dari profil)</label>
                    <select id="saved-address" class="form-select">
                        <option value="">Pilih alamat dari profil</option>
                        @foreach($addresses as $a)
                            <option value="{{ $a->id }}" data-district="{{ $a->district_id }}" data-province-name="{{ $a->province_name }}" data-city-name="{{ $a->city_name }}" data-district-name="{{ $a->district_name }}" @if($a->is_default) selected @endif>{{ $a->label ?? 'Alamat' }} - {{ Str::limit($a->address_line, 50) }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-12 mb-2 text-muted">Tidak ada alamat tersimpan — silakan tambahkan di Profil.</div>
                @endif
                <div class="col-12 mb-2">
                    <label class="form-label">Berat (otomatis dari produk)</label>
                    <input type="number" id="weight" class="form-control" placeholder="Berat (gram)" value="{{ $totalWeight ?? 0 }}" readonly>
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

    // Province/city/district selection removed — using saved profile addresses only.

    $('#btn-ongkir').click(function () {

        // Use selected saved address as destination
        const selected = $('#saved-address').find(':selected');
        const districtId = selected.data('district');
        let courier = $('input[name=courier]:checked').val();
        const weight = $('#weight').val();

        if (!districtId) {
            alert('Pilih alamat dari profil terlebih dahulu.');
            return;
        }
        if (!courier) {
            courier = 'jne';
            $('input[name=courier][value="' + courier + '"]').prop('checked', true);
        }

        const data = {
            _token: $('meta[name=csrf-token]').attr('content'),
            district_id: districtId,
            courier: courier,
            weight: weight
        };

        $('#loading-indicator').show();

        $.post('/check-ongkir', data)
            .done(res => {
                $('#results-ongkir').empty();

                // render options with a "Pilih" button to save shipping to session
                res.forEach((v, idx) => {
                    const cost = v.cost;
                    const service = v.service || v.description || `service-${idx}`;
                    const etd = v.etd || '';
                    $('#results-ongkir').append(`
                        <div class="d-flex justify-content-between border rounded p-2 mb-2 align-items-center">
                            <div>
                                <div>${service} ${etd ? '('+etd+')' : ''}</div>
                                <small class="text-muted">${v.description ?? ''}</small>
                            </div>
                            <div class="text-end">
                                <div class="mb-2"><strong>Rp ${rupiah(cost)}</strong></div>
                                <button class="btn btn-sm btn-success btn-select-shipping" data-cost="${cost}" data-service="${service}" data-etd="${etd}">Pilih</button>
                            </div>
                        </div>
                    `);
                });

                // attach click handler for choosing a shipping option
                $('.btn-select-shipping').click(function () {
                    const btn = $(this);
                        const payload = {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        cost: btn.data('cost'),
                        service: btn.data('service'),
                        etd: btn.data('etd'),
                        courier: $('input[name=courier]:checked').val(),
                        district_id: $('#saved-address').find(':selected').data('district'),
                        province: $('#saved-address').find(':selected').data('province-name') || null,
                        city: $('#saved-address').find(':selected').data('city-name') || null,
                    };

                    btn.prop('disabled', true).text('Menyimpan...');

                    $.post('/beranda/cart/shipping', payload, res2 => {
                        if (res2 && res2.shipping) {
                            const sc = parseFloat(res2.shipping.cost || 0);
                            $('#shipping-cost').text('Rp ' + rupiah(sc));
                            $('#total-price').text('Rp ' + rupiah(sc + {{ $total }}));
                        }
                    }).fail(() => alert('Gagal menyimpan pilihan ongkir.')).always(() => btn.prop('disabled', false).text('Pilih'));
                });

            })
            .fail(() => {
                alert('Gagal menghitung ongkir. Periksa koneksi atau konfigurasi API RajaOngkir.');
            })
            .always(() => $('#loading-indicator').hide());
    });

    // when user selects a saved address, auto-calc ongkir if courier+weight present
    // when user selects a saved address, auto-calc ongkir using product weight
    $('#saved-address').change(function () {
        const selected = $(this).find(':selected');
        const district = selected.data('district');

        if (!district) return;

        // ensure weight is present (prefilled from cart totalWeight)
        const autoWeight = '{{ $totalWeight ?? 0 }}';
        $('#weight').val(autoWeight);

        // if courier not selected, auto-pick a sensible default (e.g., 'jne')
        let selectedCourier = $('input[name=courier]:checked').val();
        if (!selectedCourier) {
            selectedCourier = 'jne';
            $('input[name=courier][value="' + selectedCourier + '"]').prop('checked', true);
        }

        $('#loading-indicator').show();

        const data = {
            _token: $('meta[name=csrf-token]').attr('content'),
            district_id: district,
            courier: selectedCourier,
            weight: $('#weight').val()
        };

        $.post('/check-ongkir', data)
            .done(res => {
                $('#results-ongkir').empty();
                res.forEach((v, idx) => {
                    const cost = v.cost;
                    const service = v.service || v.description || `service-${idx}`;
                    const etd = v.etd || '';
                    $('#results-ongkir').append(`
                        <div class="d-flex justify-content-between border rounded p-2 mb-2 align-items-center">
                            <div>
                                <div>${service} ${etd ? '('+etd+')' : ''}</div>
                                <small class="text-muted">${v.description ?? ''}</small>
                            </div>
                            <div class="text-end">
                                <div class="mb-2"><strong>Rp ${rupiah(cost)}</strong></div>
                                <button class="btn btn-sm btn-success btn-select-shipping" data-cost="${cost}" data-service="${service}" data-etd="${etd}">Pilih</button>
                            </div>
                        </div>
                    `);
                });

                // attach click handler (re-use same logic as manual)
                $('.btn-select-shipping').click(function () {
                    const btn = $(this);
                    const payload = {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        cost: btn.data('cost'),
                        service: btn.data('service'),
                        etd: btn.data('etd'),
                        courier: $('input[name=courier]:checked').val(),
                        district_id: district,
                        province: selected.data('province-name') || null,
                        city: selected.data('city-name') || null,
                    };

                    btn.prop('disabled', true).text('Menyimpan...');

                    $.post('/beranda/cart/shipping', payload, res2 => {
                        if (res2 && res2.shipping) {
                            const sc = parseFloat(res2.shipping.cost || 0);
                            $('#shipping-cost').text('Rp ' + rupiah(sc));
                            $('#total-price').text('Rp ' + rupiah(sc + {{ $total }}));
                        }
                    }).fail(() => alert('Gagal menyimpan pilihan ongkir.')).always(() => btn.prop('disabled', false).text('Pilih'));
                });

            })
            .fail(() => {
                alert('Gagal menghitung ongkir. Periksa koneksi atau konfigurasi API RajaOngkir.');
            })
            .always(() => $('#loading-indicator').hide());
    });

});
</script>
@endpush

