<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ShippingRateCache;
use Carbon\Carbon;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        $items = [];

        if (!empty($cart)) {
            $productIds = array_keys($cart);
            $products = Products::whereIn('id', $productIds)->get()->keyBy('id');
            $totalWeight = 0; // in grams

            foreach ($cart as $productId => $item) {
                $product = $products->get($productId);
                if ($product) {
                    $items[] = [
                        'product_id' => $productId,
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'price' => $item['harga'] ?? $item['price'],
                        'subtotal' => ($item['harga'] ?? $item['price']) * $item['quantity'],
                    ];
                    $total += ($item['harga'] ?? $item['price']) * $item['quantity'];
                    // accumulate weight
                    $prodWeight = (int) ($product->berat ?? 0);
                    $totalWeight += $prodWeight * (int)$item['quantity'];
                }
            }
        }

        // Mengambil data provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
        $provinces = [];

        // Memeriksa apakah permintaan berhasil
        if ($response->successful()) {

            // Mengambil data provinsi dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            $provinces = $response->json()['data'] ?? [];
        }


        // load saved shipping (if any) from session so frontend can display it
        $shipping = session()->get('shipping', [
            'cost' => 0,
            'service' => null,
            'courier' => null,
            'district_id' => null,
            'etd' => null,
            'province' => null,
            'city' => null,
        ]);

        $addresses = [];
        if (auth()->check()) {
            $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();
        }

        return view('pages.cart', compact('items', 'cart', 'total', 'provinces', 'shipping', 'addresses'))
            ->with('totalWeight', $totalWeight ?? 0);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Admins cannot add products to cart / purchase
        if ($request->user() && $request->user()->role === 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Admin tidak dapat melakukan pembelian.');
        }

        $validated = $request->validate([
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Products::find($validated['id']);
        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $qty = (int) $validated['quantity'];
        $cart = session()->get('cart', []);

        // existing quantity in cart
        $existing = isset($cart[$product->id]) ? (int) $cart[$product->id]['quantity'] : 0;
        $newTotal = $existing + $qty;

        if ($newTotal > $product->stok) {
            // cap to available stock
            $allowedToAdd = max(0, $product->stok - $existing);
            if ($allowedToAdd <= 0) {
                return redirect()->route('cart.index')->with('error', 'Stok tidak cukup untuk menambahkan produk ini.');
            }
            $qty = $allowedToAdd;
            session()->flash('warning', "Jumlah ditambah hanya {$qty} karena keterbatasan stok.");
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $existing + $qty;
        } else {
            $cart[$product->id] = [
                "nama" => $product->nama,
                "quantity" => $qty,
                "harga" => $product->harga,
                "gambar" => $product->gambar
            ];
        }

        session()->put('cart', $cart);

        // reserve stock immediately when added to cart
        if ($qty > 0) {
            $product->decrement('stok', $qty);
        }

        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $quantity = (int) $validated['quantity'];
        $cart = session()->get('cart', []);
        if (!isset($cart[$id])) {
            return back()->with('error', 'Produk tidak ditemukan di keranjang.');
        }

        $product = Products::find($id);
        if (!$product) {
            // product removed from catalog, refund reserved stock
            $oldQty = $cart[$id]['quantity'];
            unset($cart[$id]);
            session()->put('cart', $cart);
            if ($oldQty > 0) {
                Products::where('id', $id)->increment('stok', $oldQty);
            }
            return back()->with('error', 'Produk tidak tersedia lagi.');
        }

        $oldQty = (int) $cart[$id]['quantity'];

        if ($quantity <= 0) {
            // remove from cart and restore stock
            unset($cart[$id]);
            session()->put('cart', $cart);
            if ($oldQty > 0) {
                $product->increment('stok', $oldQty);
            }
            return back()->with('success', 'Produk dihapus dari keranjang.');
        }

        // If increasing quantity, reserve additional stock
        if ($quantity > $oldQty) {
            $diff = $quantity - $oldQty;
            if ($diff > $product->stok) {
                $diff = $product->stok;
                $quantity = $oldQty + $diff;
                session()->flash('warning', "Jumlah disesuaikan menjadi {$quantity} karena keterbatasan stok.");
            }
            if ($diff > 0) {
                $product->decrement('stok', $diff);
            }
        } elseif ($quantity < $oldQty) {
            // If decreasing, release stock
            $diff = $oldQty - $quantity;
            if ($diff > 0) {
                $product->increment('stok', $diff);
            }
        }

        $cart[$id]['quantity'] = $quantity;
        session()->put('cart', $cart);

        return back()->with('success', 'Keranjang diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $qty = (int) $cart[$id]['quantity'];
            unset($cart[$id]);
            session()->put('cart', $cart);
            if ($qty > 0) {
                // restore reserved stock
                $product = Products::find($id);
                if ($product) {
                    $product->increment('stok', $qty);
                }
            }
        }

        return redirect()->route('cart.index')->with('success', 'Produk berhasil dihapus dari keranjang.');    
    }
    

    /**
     * Mengambil data kota berdasarkan ID provinsi
     *
     * @param int $provinceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities($provinceId)
    {
        // Mengambil data kota berdasarkan ID provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

        if ($response->successful()) {

            // Mengambil data kota dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return response()->json($response->json()['data'] ?? []);
        }
    }

    /**
     * Mengambil data kecamatan berdasarkan ID kota
     *
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistricts($cityId)
    {
        // Mengambil data kecamatan berdasarkan ID kota dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

        if ($response->successful()) {

            // Mengambil data kecamatan dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return response()->json($response->json()['data'] ?? []);
        }
    }

    /**
     * Menghitung ongkos kirim berdasarkan data yang diberikan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOngkir(Request $request)
    {
        try {
            $apiKey = config('rajaongkir.api_key');
            if (empty($apiKey)) {
                Log::warning('RajaOngkir API key not configured.');
                return response()->json(['error' => 'RajaOngkir API key belum dikonfigurasi.'], 502);
            }

            $district = $request->input('district_id');
            $courier = $request->input('courier');
            $weight = (int) $request->input('weight');

            if (empty($district) || empty($courier) || $weight <= 0) {
                return response()->json(['error' => 'Parameter tidak lengkap (district, courier, weight).'], 400);
            }

            // bucket weight into 500g steps to reduce cache cardinality
            $bucketSize = 500; // grams
            $weightBucket = (int) (ceil($weight / $bucketSize) * $bucketSize);

            $cacheKey = "ongkir:{$district}:{$courier}:{$weightBucket}";

            // try in-memory/framework cache first (fast path)
            if (Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);
                return response()->json($cached);
            }

            // fallback to DB-backed cache table to share caches across instances
            $dbCache = ShippingRateCache::where('district_id', $district)
                ->where('courier', $courier)
                ->where('weight_bucket', $weightBucket)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($dbCache) {
                // populate framework cache for faster subsequent hits
                Cache::put($cacheKey, $dbCache->data, now()->addHours(6));
                return response()->json($dbCache->data);
            }

            $response = Http::asForm()->withHeaders([
                'Accept' => 'application/json',
                'key'    => $apiKey,
            ])->timeout(10)->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin'      => 1392, // ID kecamatan Diwek (ganti sesuai kebutuhan)
                'destination' => $district, // ID kecamatan tujuan
                'weight'      => $weightBucket, // use bucketed weight
                'courier'     => $courier,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                // cache the data for 6 hours in both framework cache and DB
                $ttlHours = 6;
                Cache::put($cacheKey, $data, now()->addHours($ttlHours));

                try {
                    ShippingRateCache::updateOrCreate([
                        'district_id' => $district,
                        'courier' => $courier,
                        'weight_bucket' => $weightBucket,
                    ], [
                        'data' => $data,
                        'expires_at' => Carbon::now()->addHours($ttlHours),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed writing shipping_rate_caches DB entry: ' . $e->getMessage());
                }

                return response()->json($data);
            }

            // log and return error when upstream fails
            Log::error('RajaOngkir responded with non-200', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json(['error' => 'Gagal mendapatkan data ongkir dari RajaOngkir.'], 502);

        } catch (\Exception $e) {
            Log::error('Exception when calling RajaOngkir: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghitung ongkir.'], 500);
        }
    }

    /**
     * Save selected shipping option to session so it persists with the cart
     */
    public function saveShipping(Request $request)
    {
        $validated = $request->validate([
            'cost' => 'required|numeric|min:0',
            'service' => 'required|string',
            'courier' => 'nullable|string',
            'district_id' => 'nullable|integer',
            'etd' => 'nullable|string',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $shipping = [
            'cost' => (float) $validated['cost'],
            'service' => $validated['service'] ?? null,
            'courier' => $validated['courier'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'etd' => $validated['etd'] ?? null,
            'province' => $validated['province'] ?? null,
            'city' => $validated['city'] ?? null,
        ];

        session()->put('shipping', $shipping);

        return response()->json(['status' => 'ok', 'shipping' => $shipping]);
    }
}
