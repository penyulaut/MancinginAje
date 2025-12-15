<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;

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

        return view('pages.cart', [
            'items' => $items,
            'cart' => $cart,
            'total' => $total
        ]);
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
        $products = Products::find($request->id);

        if (!$products) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $cart = session()->get('cart', []);            

        if (isset($cart[$products->id])) {
            $cart[$products->id]['quantity'] += $request->quantity;
        } else {
            $cart[$products->id] = [
                "nama" => $products->nama,
                "quantity" => $request->quantity,
                "harga" => $products->harga,
                "gambar" => $products->gambar
            ];
        }

        session()->put('cart', $cart);        

        // setelah tambah, arahkan ke halaman cart
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
        $quantity = $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            if ($quantity <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = $quantity;
            }
        }

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
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Produk berhasil dihapus dari keranjang.');    
    }
}
