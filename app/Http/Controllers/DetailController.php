<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;

class DetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.detail');
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
        // Prevent admins from adding products to cart
        $user = $request->user();
        if ($user && ($user->role ?? '') === 'admin') {
            return redirect()->route('pages.beranda')->with('error', 'Admin tidak bisa membeli barang.');
        }

        $products = Products::find($request->id);

        if (!$products) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        // Ambil data keranjang dari session
        $cart = session()->get('cart', []);

        // Jika produk sudah ada di keranjang, tambahkan jumlah
        if (isset($cart[$products->id])) {
            $cart[$products->id]['quantity'] += $request->quantity;
        } else {
            // Jika belum ada, tambahkan produk baru
            $cart[$products->id] = [
                "nama" => $products->nama,
                "quantity" => $request->quantity,
                "harga" => $products->harga,
                "gambar" => $products->gambar
            ];
        }

        // Simpan lagi ke session
        session()->put('cart', $cart);

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
