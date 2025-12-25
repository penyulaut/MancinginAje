<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('nama')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        // Slug will be auto-generated if not provided (via model boot)
        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has products
        if ($category->hasProducts()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih memiliki produk. Pindahkan atau hapus produk terlebih dahulu.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
