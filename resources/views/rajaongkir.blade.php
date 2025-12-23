<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Raja Ongkir V2 - SantriKoding.com</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <style>
        .loader{border:4px solid #f3f3f3;border-top:4px solid #4f46e5;border-radius:50%;width:30px;height:30px;animation:spin 1s linear infinite;margin:0 auto;display:none}@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
    </style>
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-xl shadow w-full max-w-2xl">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Kalkulator Ongkos Kirim (V2)</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            <!-- Dropdown Provinsi -->
            <div>
                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Provinsi Tujuan</label>
                <select id="province" name="province_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow">
                    <option value="">-- Pilih Provinsi --</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Kota / Kabupaten -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Kota / Kabupaten Tujuan</label>
                <select id="city" name="city_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                    <option value="">-- Pilih Kota / Kabupaten --</option>
                </select>
            </div>

            <!-- Dropdown Kecamatan -->
            <div>
                <label for="district" class="block text-sm font-medium text-gray-700 mb-1">Kecamatan Tujuan</label>
                <select id="district" name="district_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
            </div>

            <div>
                <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Berat Barang (gram)</label>
                <input type="number" name="weight" id="weight" min="1000" placeholder="Masukkan berat barang dalam gram" class="mt-1 block w-full pl-3 pr-3 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow">
            </div>

        </div>

        <!-- Radio Box Kurir -->
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                
                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-1" value="sicepat" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-1" class="ml-2 block text-sm text-gray-900">SICEPAT</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-2" value="jnt" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-2" class="ml-2 block text-sm text-gray-900">J&T</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-3" value="ninja" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-3" class="ml-2 block text-sm text-gray-900">Ninja Express</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-4" value="jne" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-4" class="ml-2 block text-sm text-gray-900">JNE</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-5" value="anteraja" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-5" class="ml-2 block text-sm text-gray-900">Anteraja</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-6" value="pos" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-6" class="ml-2 block text-sm text-gray-900">POS Indonesia</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-7" value="tiki" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-7" class="ml-2 block text-sm text-gray-900">Tiki</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-8" value="wahana" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-8" class="ml-2 block text-sm text-gray-900">Wahana</label>
                </div>

                <div class="flex items-center">
                    <input type="radio" name="courier" id="courier-9" value="lion" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                    <label for="courier-9" class="ml-2 block text-sm text-gray-900">Lion Parcel</label>
                </div>

            </div>
        </div>

        <div class="flex justify-center mb-8 flex-col items-center">
            <button class="btn-check w-full md:w-auto px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                Hitung Ongkos Kirim
            </button>
            <div class="loader mt-4" id="loading-indicator"></div>
        </div>

        <!-- Hasil Perhitungan Ongkos Kirim -->
        <div class="mt-8 p-6 bg-indigo-50 border border-indigo-200 rounded-lg results-container hidden">
            <h2 class="text-xl font-semibold text-indigo-800 mb-4 text-center">Hasil Perhitungan Ongkos Kirim</h2>
            <div class="space-y-3" id="results-ongkir">
            </div>
        </div>

        <script>
            $(document).ready(function() {

                // Fungsi formatCurrency
                function formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount);
                }

                // Inisialisasi dropdown kota/kabupaten
                $('select[name="province_id"]').on('change', function() {
                    let provinceId = $(this).val();
                    if (provinceId) {
                        jQuery.ajax({
                            url: `/cities/${provinceId}`,
                            type: "GET",
                            dataType: "json",
                            success: function(response) {
                                $('select[name="city_id"]').empty();
                                $('select[name="city_id"]').append(`<option value="">-- Pilih Kota / Kabupaten --</option>`);
                                $.each(response, function(index, value) {
                                    $('select[name="city_id"]').append(`<option value="${value.id}">${value.name}</option>`);
                                });
                            }
                        });
                    } else {
                        $('select[name="city_id"]').append(`<option value="">-- Pilih Kota / Kabupaten --</option>`);
                    }
                });

                // Inisialisasi dropdown kecamatan
                $('select[name="city_id"]').on('change', function() {
                    let cityId = $(this).val();
                    if (cityId) {
                        jQuery.ajax({
                            url: `/districts/${cityId}`,
                            type: "GET",
                            dataType: "json",
                            success: function(response) {
                                $('select[name="district_id"]').empty();
                                $('select[name="district_id"]').append(`<option value="">-- Pilih Kecamatan --</option>`);
                                $.each(response, function(index, value) {
                                    $('select[name="district_id"]').append(`<option value="${value.id}">${value.name}</option>`);
                                });
                            }
                        });
                    } else {
                        $('select[name="district_id"]').append(`<option value="">-- Pilih Kecamatan --</option>`);
                    }
                });

                // ajax check ongkir
                let isProcessing = false;

                $('.btn-check').click(function (e) {
                    e.preventDefault();

                    if (isProcessing) return;

                    let token        = $("meta[name='csrf-token']").attr("content");
                    let district_id  = $('select[name=district_id]').val();
                    let courier      = $('input[name=courier]:checked').val();
                    let weight       = $('#weight').val();

                    // Validasi form
                    if (!district_id || !courier || !weight) {
                        alert('Harap lengkapi semua data terlebih dahulu!');
                        return;
                    }

                    isProcessing = true;
                    
                    // Tampilkan loading indicator
                    $('#loading-indicator').show();
                    $('.btn-check').prop('disabled', true);
                    $('.btn-check').text('Memproses...');

                    $.ajax({
                        url: "/check-ongkir",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            _token: token,
                            district_id: district_id,
                            courier: courier,
                            weight: weight,
                        },
                        beforeSend: function() {
                            // Sembunyikan hasil sebelumnya jika ada
                            $('.results-container').addClass('hidden').removeClass('block');
                        },
                        success: function (response) {
                            if (response) {
                                $('#results-ongkir').empty();
                                $('.results-container').removeClass('hidden').addClass('block');
                                $.each(response, function (index, value) {
                                    $('#results-ongkir').append(`
                                        <div class="flex justify-between items-center p-3 bg-white rounded-xl shadow border border-gray-200">
                                            <span class="text-lg font-medium text-gray-800">${value.service} - ${value.description} - (${value.etd})</span>
                                            <span class="text-lg font-bold text-indigo-700">${formatCurrency(value.cost)}</span>
                                        </div>
                                    `);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Gagal menghitung ongkir:", error);
                            alert("Terjadi kesalahan saat menghitung ongkir. Coba lagi.");
                        },
                        complete: function () {
                            // Sembunyikan loading indicator
                            $('#loading-indicator').hide();
                            $('.btn-check').prop('disabled', false);
                            $('.btn-check').text('Hitung Ongkos Kirim');
                            
                            // pastikan tombol bisa diklik kembali
                            isProcessing = false;
                        }
                    });
                });

            });
        </script>

    </body>
</html>
