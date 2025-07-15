<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // // Filter berdasarkan kategori jika tersedia di request
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // // Cari berdasarkan nama produk
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // // Ambil data produk dengan pagination
        $products = $query->orderBy('name')->paginate(10);

        // // Ambil semua kategori untuk filter
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // // Validasi tambahan: harga jual tidak boleh lebih kecil dari harga beli
        if ($request->cost_price > $request->selling_price) {
            return back()
                ->withErrors(['selling_price' => 'Harga jual harus lebih besar atau sama dengan harga beli.'])
                ->withInput();
        }

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // // Validasi tambahan: harga jual tidak boleh lebih kecil dari harga beli
        if ($request->cost_price > $request->selling_price) {
            return back()
                ->withErrors(['selling_price' => 'Harga jual harus lebih besar atau sama dengan harga beli.'])
                ->withInput();
        }

        $product->update($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Cek apakah produk memiliki transaksi terkait
    $hasTransactions = 
        $product->saleDetails()->exists() ||
        $product->purchaseDetails()->exists() ||
        $product->salesAggregates()->exists() ||
        $product->forecastSES()->exists() ||
        $product->inventoryMovements()->exists();

    if ($hasTransactions) {
        return redirect()->route('products.index')
            ->with('error', 'Produk tidak bisa dihapus karena memiliki produk ini memiliki data yang berkaitan dengan transaksi!');
    }
        // Hapus produk jika tidak ada transaksi terkait
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
