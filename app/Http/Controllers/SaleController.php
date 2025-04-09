<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('user')->orderBy('date', 'desc')->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $invoice = 'INV-' . date('YmdHis');
        return view('sales.create', compact('products', 'invoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:sales',
            'customer_name' => 'required|string|max:255',
            'service_price' => 'nullable|numeric|min:0',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalProductPrice = 0;
            $servicePrice = $request->service_price ?? 0;

            // Create sale
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'date' => Carbon::now(),
                'customer_name' => $request->customer_name,
                'service_price' => $servicePrice,
                'total_price' => 0, // Will update after calculating product prices
                'user_id' => auth()->id(),
            ]);

            // Process each product
            foreach ($request->products as $productData) {
                if (!isset($productData['id']) || !isset($productData['quantity']) || $productData['quantity'] <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productData['id']);
                
                // Check if stock is sufficient
                if ($product->stock < $productData['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }

                $subtotal = $product->selling_price * $productData['quantity'];
                $totalProductPrice += $subtotal;

                // Create sale detail
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product->stock -= $productData['quantity'];
                $product->save();

                // Record inventory movement
                InventoryMovement::create([
                    'date' => Carbon::now(),
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'movement_type' => 'out',
                    'reference' => 'Sale: ' . $request->invoice_number,
                ]);
            }

            // Update total price
            $sale->total_price = $totalProductPrice + $servicePrice;
            $sale->save();

            DB::commit();

            return redirect()->route('sales.show', $sale->id)
                ->with('success', 'Transaksi penjualan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['details.product', 'user']);
        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['details.product', 'user']);
        return view('sales.invoice', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();

        try {
            // Restore product stock
            foreach ($sale->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock += $detail->quantity;
                    $product->save();

                    // Record inventory movement
                    InventoryMovement::create([
                        'date' => Carbon::now(),
                        'product_id' => $product->id,
                        'quantity' => $detail->quantity,
                        'movement_type' => 'in',
                        'reference' => 'Sale Canceled: ' . $sale->invoice_number,
                    ]);
                }
            }

            // Delete sale details
            $sale->details()->delete();
            
            // Delete sale
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Transaksi penjualan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
