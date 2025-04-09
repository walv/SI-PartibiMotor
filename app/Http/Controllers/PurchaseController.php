<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::orderBy('date', 'desc')->paginate(10);
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

            //  buat pembelian produk untuk stok barang
            $purchase = Purchase::create([
                'invoice_number' => $request->invoice_number,
                'date' => Carbon::now(),
                'supplier_name' => $request->supplier_name,
                'total_price' => 0, 
                'user_id' => auth()->id(),
            ]);

            // proses tiap produk
            foreach ($request->products as $productData) {
                if (!isset($productData['id']) || !isset($productData['quantity']) || !isset($productData['price']) || $productData['quantity'] <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productData['id']);
                $subtotal = $productData['price'] * $productData['quantity'];
                $totalPrice += $subtotal;

                // detail pembelian produk
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $subtotal,
                ]);

                // update harga sesuai dengan pembelian terakhir
                $product->stock += $productData['quantity'];
                $product->cost_price = $productData['price']; 
                $product->save();

                // merekam pergerakan stok 
                InventoryMovement::create([
                    'date' => Carbon::now(),
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'movement_type' => 'in',
                    'reference' => 'Purchase: ' . $request->invoice_number,
                ]);
            }

            // total harga
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
        $purchase->load(['details.product', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();

        try {
            // Mengembalikan stok produk
            foreach ($purchase->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock -= $detail->quantity;
                    $product->save();

                    // Catat pergerakan stok
                    InventoryMovement::create([
                        'date' => Carbon::now(),
                        'product_id' => $product->id,
                        'quantity' => $detail->quantity,
                        'movement_type' => 'out',
                        'reference' => 'Purchase Canceled: ' . $purchase->invoice_number,
                    ]);
                }
            }

            // hapus detail pembelian
            $purchase->details()->delete();
            
            // hapus pembelian
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
