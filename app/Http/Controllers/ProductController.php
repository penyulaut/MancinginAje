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
        $search = trim($request->input('search'));

        // Query produk dengan filter pencarian yang lebih pintar
        $products = Products::query();

        if ($search) {
            // Split search terms untuk multiple keywords
            $searchTerms = explode(' ', $search);

            $products = $products->where(function($query) use ($search, $searchTerms) {
                // Search di nama produk (case-insensitive)
                $query->whereRaw('LOWER(nama) LIKE LOWER(?)', ["%{$search}%"]);

                // Search di deskripsi (case-insensitive)
                $query->orWhereRaw('LOWER(deskripsi) LIKE LOWER(?)', ["%{$search}%"]);

                // Search di kategori (case-insensitive)
                $query->orWhereHas('category', function($q) use ($search) {
                    $q->whereRaw('LOWER(nama) LIKE LOWER(?)', ["%{$search}%"]);
                });

                // Multiple keywords search
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) { // Minimal 3 karakter
                        $query->orWhereRaw('LOWER(nama) LIKE LOWER(?)', ["%{$term}%"])
                              ->orWhereRaw('LOWER(deskripsi) LIKE LOWER(?)', ["%{$term}%"]);
                    }
                }
            });
        }

        $products = $products->paginate(12)->appends(request()->query());

        // Ambil semua kategori untuk ditampilkan (dengan cache) dan muat produknya
        $categories = Cache::remember('categories_with_products', 3600, function () {
            return Category::with('products')->get();
        });

        return view('pages.orders', [
            'products' => $products,
            'categories' => $categories,
            'searchTerm' => $search, // Kirim search term ke view
        ]);
    }

    /**
     * API endpoint untuk search suggestions
     */
    public function searchSuggestions(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // Cari produk berdasarkan nama
        $products = Products::whereRaw('LOWER(nama) LIKE LOWER(?)', ["%{$query}%"])
                           ->limit(5)
                           ->get(['nama', 'id']);

        foreach ($products as $product) {
            $suggestions[] = [
                'text' => $product->nama,
                'type' => 'product',
                'url' => route('products.show', $product->id)
            ];
        }

        // Cari kategori
        $categories = Category::whereRaw('LOWER(nama) LIKE LOWER(?)', ["%{$query}%"])
                             ->limit(3)
                             ->get(['nama', 'id']);

        foreach ($categories as $category) {
            $suggestions[] = [
                'text' => 'Kategori: ' . $category->nama,
                'type' => 'category',
                'url' => route('pages.orders') . '?search=' . urlencode($category->nama)
            ];
        }

        // Jika tidak ada hasil, berikan suggestions umum
        if (empty($suggestions)) {
            $commonSearches = ['joran', 'reel', 'kail', 'umpan', 'pakaian'];
            $matching = array_filter($commonSearches, function($item) use ($query) {
                return stripos($item, $query) !== false;
            });

            foreach (array_slice($matching, 0, 3) as $suggestion) {
                $suggestions[] = [
                    'text' => $suggestion,
                    'type' => 'suggestion',
                    'url' => route('pages.orders') . '?search=' . urlencode($suggestion)
                ];
            }
        }

        return response()->json($suggestions);
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
