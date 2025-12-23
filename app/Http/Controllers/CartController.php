<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Http;


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


        return view('pages.cart', compact('items', 'cart', 'total', 'provinces'));

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
        
        $response = Http::asForm()->withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key'    => config('rajaongkir.api_key'),

        ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin'      => 1392, // ID kecamatan Diwek (ganti sesuai kebutuhan)
                'destination' => $request->input('district_id'), // ID kecamatan tujuan
                'weight'      => $request->input('weight'), // Berat dalam gram
                'courier'     => $request->input('courier'), // Kode kurir (jne, tiki, pos)
        ]);

        if ($response->successful()) {

            // Mengambil data ongkos kirim dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            return $response->json()['data'] ?? [];
        }
    }
}
