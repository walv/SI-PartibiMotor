<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleServiceDetail;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ManualImportController extends Controller
{
    public function create()
    {
        $products = Product::all();
        $services = Service::all();
        // Tampilkan form untuk import manual
        return view('exportimport.import_manual', compact('products', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'products.*.id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|numeric|min:1',
            'services.*.id' => 'nullable|exists:services,id',
            'services.*.price' => 'nullable|numeric|min:1',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Simpan data ke tabel `sales`
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name,
                'date' => $request->date,
                'total_price' => 0, // Akan dihitung nanti
                'user_id' => auth()->id(),
            ]);
    
            $totalProduct = 0;
            $totalService = 0;
    
            // Simpan detail produk
            if (!empty($request->products)) {
                foreach ($request->products as $product) {
                    $productModel = Product::findOrFail($product['id']);
                    $subtotal = $productModel->selling_price * $product['quantity'];
    
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productModel->id,
                        'quantity' => $product['quantity'],
                        'price' => $productModel->selling_price,
                        'subtotal' => $subtotal,
                    ]);
    
                    $totalProduct += $subtotal;
    
                    // Update atau buat Sales Aggregate
                    $period = \Carbon\Carbon::parse($request->date)->format('Y-m'); // Ambil periode dari tanggal transaksi
                    $salesAggregate = \App\Models\SalesAggregate::where('product_id', $productModel->id)
                        ->where('period', $period)
                        ->first();
    
                    if ($salesAggregate) {
                        // Update agregat yang sudah ada
                        $salesAggregate->total_sales += $product['quantity'];
                        $salesAggregate->total_price += $subtotal;
                        $salesAggregate->save();
                    } else {
                        // Jika belum ada, buat entri baru untuk sales_aggregate
                        \App\Models\SalesAggregate::create([
                            'product_id' => $productModel->id,
                            'period' => $period,
                            'total_sales' => $product['quantity'],
                            'total_price' => $subtotal,
                        ]);
                    }
                }
            }
    
            // Simpan detail jasa
            if (!empty($request->services)) {
                foreach ($request->services as $service) {
                    $serviceModel = Service::findOrFail($service['id']);
                    $subtotal = $service['price'];
    
                    SaleServiceDetail::create([
                        'sale_id' => $sale->id,
                        'service_id' => $serviceModel->id,
                        'price' => $service['price'],
                        'subtotal' => $subtotal,
                    ]);
    
                    $totalService += $subtotal;
                }
            }
    
            // Update total harga di tabel `sales`
            $sale->update([
                'total_price' => $totalProduct + $totalService,
            ]);
    
            DB::commit();
    
            return redirect()->route('sales.import.manual')->with('success', 'Data penjualan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('sales.import.manual')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
}   
}