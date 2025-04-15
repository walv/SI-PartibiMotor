<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\InventoryMovement;
use App\Models\SalesAggregate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user');

        // Pencarian berdasarkan invoice atau customer
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tanggal
        if ($request->has('date') && !empty($request->date)) {
            $date = $request->date;
            $query->whereDate('date', $date);
        }

        $sales = $query->orderBy('date', 'desc')->paginate(10);
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
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
        ]);
    
        DB::beginTransaction();
        try {
            $totalProductPrice = 0;
            $servicePrice = $request->service_price ?? 0;
    
            // Buat transaksi penjualan
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'date' => Carbon::now(),
                'customer_name' => $request->customer_name ?? 'Pelanggan',
                'service_price' => $servicePrice,
                'total_price' => 0,  // Akan diupdate setelah menghitung harga produk
                'user_id' => auth()->id(),
            ]);
    
            // Proses penjualan setiap produk
            foreach ($request->products as $productData) {
                if (!isset($productData['id']) || !isset($productData['quantity']) || $productData['quantity'] <= 0) {
                    continue;
                }
    
                $product = Product::findOrFail($productData['id']);
                
                // Cek jika stok mencukupi
                if ($product->stock < $productData['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }
    
                $subtotal = $product->selling_price * $productData['quantity'];
                $totalProductPrice += $subtotal;
    
                // Buat detail penjualan
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);
    
                // Simpan stok sebelum diupdate
                $stockBefore = $product->stock;
                
                // Update stok produk
                $product->stock -= $productData['quantity'];
                $product->save();
                
                // Catat pergerakan inventaris
                InventoryMovement::create([
                    'date' => Carbon::now(),
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'movement_type' => 'out',
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $productData['quantity'],
                    'reference' => 'Sale: ' . $request->invoice_number,
                ]);
    
                // Update atau buat Sales Aggregate untuk produk ini
                $period = Carbon::now()->format('Y-m'); // Ambil periode dari bulan dan tahun saat ini

                // Cek apakah sudah ada agregat untuk produk ini di periode yang sama
                $salesAggregate = SalesAggregate::where('product_id', $product->id)
                    ->where('period', $period)
                    ->first();
                
                if ($salesAggregate) {
                    // Update agregat yang sudah ada
                    $salesAggregate->total_sales += $productData['quantity'];
                    $salesAggregate->total_price += $subtotal;
                    $salesAggregate->save();
                } else {
                    // Jika belum ada, buat entri baru untuk sales_aggregate
                    SalesAggregate::create([
                        'product_id' => $product->id,
                        'period' => $period,
                        'total_sales' => $productData['quantity'],
                        'total_price' => $subtotal,
                    ]);
                }
            }
    
            // Update total harga penjualan
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
        // Load relasi yang diperlukan untuk penjualan
        $sale->load(['saleDetails.product', 'user']);
        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        // Load relasi yang diperlukan untuk invoice
        $sale->load(['saleDetails.product', 'user']);
        return view('sales.invoice', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            // Mengembalikan stok produk
            foreach ($sale->saleDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    // Simpan stok sebelum diupdate
                    $stockBefore = $product->stock;
                    
                    // Update stok produk
                    $product->stock += $detail->quantity;
                    $product->save();
                    
                    // Catat pergerakan inventaris
                    InventoryMovement::create([
                        'date' => Carbon::now(),
                        'product_id' => $product->id,
                        'quantity' => $detail->quantity,
                        'movement_type' => 'in',
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore + $detail->quantity,
                        'reference' => 'Sale Canceled: ' . $sale->invoice_number,
                    ]);
                }
            }

            // Hapus detail penjualan dan penjualan
            $sale->saleDetails()->delete();
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
