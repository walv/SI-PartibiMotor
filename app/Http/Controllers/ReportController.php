<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Reports\SalesReportExport;
use App\Exports\Reports\PurchasesReportExport;
use App\Exports\Reports\FinancialReportExport;
use App\Exports\Reports\InventoryReportExport;



class ReportController extends Controller
{
    /**
     * Display sales report.
     */
    public function sales(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        // Get sales data
       $sales = Sale::whereBetween('date', [$startDate, $endDate])
        ->with(['user', 'saleDetails.product']) // Tambahkan ini
        ->orderBy('date', 'desc')
        ->get();

        // hitung total penjualan
        $totalSales = $sales->sum('total_price');
        $totalServiceFee = $sales->sum('service_price');
        $totalProductSales = $totalSales - $totalServiceFee;
        $totalTransactions = $sales->count();

        // top produk
        $topProducts = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->select(
                'sale_details.product_id',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.subtotal) as total_amount')
            )
            ->groupBy('sale_details.product_id')
            ->orderBy('total_quantity', 'desc')
            ->with('product')
            ->take(5)
            ->get();

        // chart penjualan harian
        $dailySales = Sale::whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as sale_date'),
                DB::raw('SUM(total_price) as total_amount')
            )
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

        // masukan 0 jika data tidak ada
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime($endDate->addDay())
        );

        $chartData = [];
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartData[$dateString] = $dailySales[$dateString] ?? 0;
        }

        return view('reports.sales', compact(
            'sales',
            'startDate',
            'endDate',
            'totalSales',
            'totalServiceFee',
            'totalProductSales',
            'totalTransactions',
            'topProducts',
            'chartData'
        ));
    }

    /**
     * Display purchases report.
     */
    public function purchases(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        // Get purchases data
        $purchases = Purchase::whereBetween('date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary
        $totalPurchases = $purchases->sum('total_price');
        $totalTransactions = $purchases->count();

        // Get top products
        $topProducts = PurchaseDetail::join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->whereBetween('purchases.date', [$startDate, $endDate])
            ->select(
                'purchase_details.product_id',
                DB::raw('SUM(purchase_details.quantity) as total_quantity'),
                DB::raw('SUM(purchase_details.subtotal) as total_amount')
            )
            ->groupBy('purchase_details.product_id')
            ->orderBy('total_quantity', 'desc')
            ->with('product')
            ->take(5)
            ->get();

        // Get daily purchases for chart
        $dailyPurchases = Purchase::whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as purchase_date'),
                DB::raw('SUM(total_price) as total_amount')
            )
            ->groupBy('purchase_date')
            ->orderBy('purchase_date')
            ->get()
            ->keyBy('purchase_date')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

        // Fill in missing dates with zero
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime($endDate->addDay())
        );

        $chartData = [];
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartData[$dateString] = $dailyPurchases[$dateString] ?? 0;
        }

        return view('reports.purchases', compact(
            'purchases',
            'startDate',
            'endDate',
            'totalPurchases',
            'totalTransactions',
            'topProducts',
            'chartData'
        ));
    }

    /**
     * Display financial report.
     */
    public function financial(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        // sales data
        $sales = Sale::whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as transaction_date'),
                DB::raw('SUM(total_price) as total_amount')
            )
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->keyBy('transaction_date')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

        // purchases data
        $purchases = Purchase::whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as transaction_date'),
                DB::raw('SUM(total_price) as total_amount')
            )
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->keyBy('transaction_date')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

        //hitung profit
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime($endDate->addDay())
        );

        $financialData = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $income = $sales[$dateString] ?? 0;
            $expense = $purchases[$dateString] ?? 0;
            $profit = $income - $expense;

            $financialData[] = [
                'date' => $dateString,
                'income' => $income,
                'expense' => $expense,
                'profit' => $profit
            ];

            $totalIncome += $income;
            $totalExpense += $expense;
        }

        $totalProfit = $totalIncome - $totalExpense;
        $profitMargin = $totalIncome > 0 ? ($totalProfit / $totalIncome) * 100 : 0;

        // buat data untuk chart
        $chartData = [
            'dates' => array_column($financialData, 'date'),
            'income' => array_column($financialData, 'income'),
            'expense' => array_column($financialData, 'expense'),
            'profit' => array_column($financialData, 'profit')
        ];

        return view('reports.financial', compact(
            'financialData',
            'startDate',
            'endDate',
            'totalIncome',
            'totalExpense',
            'totalProfit',
            'profitMargin',
            'chartData'
        ));
    }

    /**
     * Display inventory report.
     */
    public function inventory(Request $request)
    {
        $categoryId = $request->category_id;
        $lowStock = $request->has('low_stock');

        // Get categories for filter
        $categories = Category::all();

        // Build query
        $query = Product::with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStock) {
            $query->where('stock', '<', 5);
        }

        $products = $query->orderBy('name')->get();

        // kalkulasi total item dan nilai inventori
        $totalItems = $products->sum('stock');
        $inventoryValue = $products->sum(function ($product) {
            return $product->stock * $product->cost_price;
        });

        // info terbaru pergerakan inventori
        $recentMovements = InventoryMovement::with(['product'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // informasi stok per kategori
        $stockByCategory = Product::select('category_id', DB::raw('SUM(stock) as total_stock'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name,
                    'stock' => $item->total_stock
                ];
            });

        return view('reports.inventory', compact(
            'products',
            'categories',
            'categoryId',
            'lowStock',
            'totalItems',
            'inventoryValue',
            'recentMovements',
            'stockByCategory'
        ));
    }

    //ekspor laporan
  
  public function exportSales(Request $request)
{
    $data = $this->sales($request)->getData();
    
    // Data sudah berupa array, tidak perlu toArray() lagi
    $exportData = [
        'sales' => $data['sales'], // Langsung gunakan array
        'startDate' => $data['startDate'],
        'endDate' => $data['endDate']
    ];
    
    $fileName = 'laporan_penjualan_' . $data['startDate']->format('Y-m-d') . '_to_' . $data['endDate']->format('Y-m-d') . '.xlsx';
    
    return Excel::download(new SalesReportExport($exportData), $fileName);
}

    // Ekspor laporan pembelian

public function exportPurchases(Request $request)
{
    $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date'
    ]);

    $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
    $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

    // Query dengan eager loading
    $purchases = Purchase::whereBetween('date', [$startDate, $endDate])
        ->with(['user', 'purchaseDetails.product']) // Pastikan relasi diload
        ->orderBy('date', 'desc')
        ->get();

    // Format data untuk export
    $exportData = [
        'purchases' => $purchases->map(function($purchase) {
            return [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'supplier_name' => $purchase->supplier_name,
                'date' => $purchase->date,
                'total_price' => $purchase->total_price,
                'purchase_details' => $purchase->purchaseDetails->map(function($detail) {
                    return [
                        'product' => $detail->product ? $detail->product->only('name') : null,
                        'quantity' => $detail->quantity,
                        'subtotal' => $detail->subtotal
                    ];
                })->toArray()
            ];
        })->toArray(),
        'startDate' => $startDate,
        'endDate' => $endDate
    ];

    $fileName = 'laporan_pembelian_'.$startDate->format('Y-m-d').'_to_'.$endDate->format('Y-m-d').'.xlsx';
    
    return Excel::download(new PurchasesReportExport($exportData), $fileName);
}

public function exportFinancial(Request $request)
{
    try {
        $data = $this->financial($request)->getData();
        $fileName = 'laporan_keuangan_'.$data['startDate']->format('Y-m-d').'_to_'.$data['endDate']->format('Y-m-d').'.xlsx';
        
        return Excel::download(
            new FinancialReportExport($data), 
            $fileName
        );
    } catch (\Exception $e) {
        return back()->withError('gagal export data: '.$e->getMessage());
    }
}

public function exportInventory(Request $request)
{
    try {
        $data = $this->inventory($request)->getData();
        $fileName = 'laporan_inventori_'.now()->format('Y-m-d').'.xlsx';
        
        return Excel::download(
            new InventoryReportExport($data), 
            $fileName
        );
    } catch (\Exception $e) {
        return back()->withError('gagal eksport data: '.$e->getMessage());
    }
}
}
