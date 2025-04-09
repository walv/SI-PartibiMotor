<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('user');
        
        // Filter berdasarkan invoice atau supplier
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('date') && !empty($request->date)) {
            $date = $request->date;
            $query->whereDate('date', $date);
        }
        
        $purchases = $query->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $invoice = 'PO-' . date('YmdHis');
        return view('purchases.create', compact('products', 'invoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:purchases',
            'supplier_name' => 'required|string|max:255',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            // Buat pembelian produk untuk stok barang
            $purchase = Purchase::create([
                'invoice_number' => $request->invoice_number,
                'date' => Carbon::now(),
                'supplier_name' => $request->supplier_name,
                'total_price' => 0,
                'user_id' => auth()->id(),
            ]);

            // Proses tiap produk
            foreach ($request->products as $productData) {
                if (!isset($productData['id']) || !isset($productData['quantity']) || !isset($productData['price']) || $productData['quantity'] <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productData['id']);
                $subtotal = $productData['price'] * $productData['quantity'];
                $totalPrice += $subtotal;

                // Detail pembelian produk
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $subtotal,
                ]);

                // Simpan stok sebelum diupdate untuk digunakan di inventory movement
                $stockBefore = $product->stock;

                // Update harga sesuai dengan pembelian terakhir
                $product->stock += $productData['quantity'];
                $product->cost_price = $productData['price'];
                $product->save();

                // Merecord pergerakan stok
                InventoryMovement::create([
                    'date' => Carbon::now(),
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'movement_type' => 'in',
                    'reference_id' => $purchase->id,
                    'reference_type' => 'purchase',
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore + $productData['quantity'],
                    'reference' => 'Purchase: ' . $request->invoice_number, // Optional: Menyimpan informasi tentang invoice
                ]);
            }

            // Total harga
            $purchase->total_price = $totalPrice;
            $purchase->save();

            DB::commit();
            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Transaksi pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['purchaseDetails.product', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // Mengembalikan stok produk
            foreach ($purchase->purchaseDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    // Simpan stok sebelum diupdate
                    $stockBefore = $product->stock;
                    
                    $product->stock -= $detail->quantity;
                    $product->save();
                    
                    // Catat pergerakan stok
                    InventoryMovement::create([
                        'date' => Carbon::now(),
                        'product_id' => $product->id,
                        'quantity' => $detail->quantity,
                        'movement_type' => 'out',
                        'reference_id' => $purchase->id,
                        'reference_type' => 'purchase',
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore - $detail->quantity,
                        'reference' => 'Purchase Canceled: ' . $purchase->invoice_number,
                    ]);
                }
            }

            // Hapus detail pembelian
            $purchase->purchaseDetails()->delete();
            // Hapus pembelian
            $purchase->delete();

            DB::commit();
            return redirect()->route('purchases.index')
                ->with('success', 'Transaksi pembelian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
