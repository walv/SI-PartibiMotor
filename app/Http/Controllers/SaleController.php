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
use App\Models\Service;
use App\Models\SaleServiceDetail;


class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user');

        // Pencarian berdasarkan invoice atau customer
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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
        $services = Service::orderBy('name')->get(); // Pastikan model Service ada
        $invoice = 'INV-' . date('YmdHis');
        return view('sales.create', compact('products', 'services', 'invoice'));
    }

    public function store(Request $request)
{
    // Validasi input (tidak diubah)
    $request->validate([
        'invoice_number' => 'required|string|max:255',
        'customer_name' => 'nullable|string|max:255',
        'products.*.id' => 'nullable|exists:products,id',
        'products.*.quantity' => 'nullable|numeric|min:1',
        'services.*.id' => 'nullable|exists:services,id',
        'services.*.price' => 'nullable|numeric|min:1',
        'discount_amount' => 'nullable|numeric|min:0',
        'description' => 'nullable|string|max:500'
    ], [
        'services.*.price.numeric' => 'Harga jasa harus berupa angka.',
        'services.*.price.min' => 'Harga jasa tidak boleh kurang dari 0.',
    ]);

    DB::beginTransaction();

    try {
        // [PERBAIKAN 1] Simpan diskon dan deskripsi SEKALI SAJA di awal dengan nilai final
        $totalProduct = 0;
        $totalService = 0;
        
        // Hitung subtotal produk terlebih dahulu
        if (!empty($request->products)) {
            foreach ($request->products as $product) {
                $productModel = Product::findOrFail($product['id']);
                $totalProduct += $productModel->selling_price * $product['quantity'];
            }
        }

        // Hitung subtotal jasa
        if (!empty($request->services)) {
            foreach ($request->services as $service) {
                $price = $service['price'] ?? Service::find($service['id'])->price;
                $totalService += $price;
            }
        }

        $subtotal = $totalProduct + $totalService;
        $finalDiscount = min($request->discount_amount ?? 0, $subtotal);

        // Buat transaksi dengan nilai final
        $sale = Sale::create([
            'invoice_number' => $request->invoice_number,
            'customer_name' => $request->customer_name,
            'total_price' => $subtotal - $finalDiscount, // Langsung hitung total akhir
            'user_id' => auth()->id(),
            'date' => now(),
            'discount_amount' => $finalDiscount, // Pakai nilai yang sudah difinalisasi
            'description' => $request->description
        ]);

        // Proses produk (tidak diubah)
        if (!empty($request->products)) {
            foreach ($request->products as $product) {
                $productModel = Product::findOrFail($product['id']);
                
                if ($productModel->stock < $product['quantity']) {
                    throw new \Exception("Stok {$productModel->name} tidak cukup");
                }

                $subtotal = $productModel->selling_price * $product['quantity'];

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productModel->id,
                    'quantity' => $product['quantity'],
                    'price' => $productModel->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $stockBefore = $productModel->stock;
                $productModel->decrement('stock', $product['quantity']);

                InventoryMovement::create([
                    'date' => now(),
                    'product_id' => $productModel->id,
                    'quantity' => $product['quantity'],
                    'movement_type' => 'out',
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $product['quantity'],
                    'reference' => 'Sale: ' . $request->invoice_number,
                ]);

                // Update sales aggregate (tidak diubah)
                $period = now()->format('Y-m');
                SalesAggregate::updateOrCreate(
                    ['product_id' => $productModel->id, 'period' => $period],
                    [
                        'total_sales' => DB::raw("total_sales + {$product['quantity']}"),
                        'total_price' => DB::raw("total_price + {$subtotal}")
                    ]
                );
            }
        }

        // Proses jasa (tidak diubah)
        if (!empty($request->services)) {
            foreach ($request->services as $service) {
                $serviceModel = Service::findOrFail($service['id']);
                $price = $service['price'] ?? $serviceModel->price;
                $subtotal = $price;

                SaleServiceDetail::create([
                    'sale_id' => $sale->id,
                    'service_id' => $serviceModel->id,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }
        }

        DB::commit();

        return redirect()->route('sales.index')
            ->with('success', 'Transaksi berhasil disimpan.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    public function show(Sale $sale)
    {
        // Load relasi yang diperlukan untuk penjualan
        $sale->load(['saleDetails.product', 'saleServiceDetails.service', 'user']);
        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        // Load relasi yang diperlukan untuk invoice
        $sale->load(['saleDetails.product', 'saleServiceDetails.service', 'user']);
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
