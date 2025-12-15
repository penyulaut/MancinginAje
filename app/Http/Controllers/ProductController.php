<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil input pencarian dari form
        $search = $request->input('search');

        // Query produk dengan filter pencarian
        $products = Products::query();
        
        if ($search) {
            $products = $products->where('nama', 'LIKE', "%{$search}%");
        }
        
        $products = $products->paginate(12);
        
        // Ambil semua kategori untuk ditampilkan (dengan cache)
        $categories = Cache::remember('categories', 3600, function () {
            return Category::all();
        });

        return view('pages.orders', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'gambar' => 'nullable|image|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $filename, 'public');
            $validated['gambar'] = 'storage/' . $path;
        }

        Products::create($validated);

        return redirect()->route('dashboard.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Products::with('category')->findOrFail($id);
        return view('pages.detail-product', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $products = Products::all();
        $productsDetail = Products::findOrFail($id);
        return view('dashboard.create', compact('products', 'productsDetail'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'gambar' => 'nullable|image|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $filename, 'public');
            $validated['gambar'] = 'storage/' . $path;
        }

        Products::where('id', $id)->update($validated);

        return redirect()->route('dashboard.index')->with('success', 'Produk berhasil diperbaharui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    
}
