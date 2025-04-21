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
            ->with('user')
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
        
        // Get sales data
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
        
        // Get purchases data
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
        
        // Calculate daily profit
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
        
        // Prepare chart data
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
        
        // Calculate inventory value
        $totalItems = $products->sum('stock');
        $inventoryValue = $products->sum(function ($product) {
            return $product->stock * $product->cost_price;
        });
        
        // Get recent movements
        $recentMovements = InventoryMovement::with(['product'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();
        
        // Get stock distribution by category
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
}
