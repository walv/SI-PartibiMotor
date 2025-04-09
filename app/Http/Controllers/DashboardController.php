<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
//jangan lupakan komentar untuk lebih jelas
class DashboardController extends Controller
{
    public function index()
    {
        // Statistik penjualan
        $totalSales = Sale::sum('total_price');
        $totalPurchases = Purchase::sum('total_price');
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        // menentukan produk terlaris
        $topProducts = SaleDetail::select('products.name', DB::raw('SUM(sale_details.quantity) as total_quantity'))
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Produk stok menipis
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Data untuk grafik (6 bulan terakhir)
        $months = collect([]);
        $salesData = collect([]);
        $purchasesData = collect([]);

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($month->format('M Y'));

            // Data penjualan per bulan - Ubah total_amount menjadi total_price
            $monthlySales = Sale::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('total_price');
            $salesData->push($monthlySales);

            // Data pembelian per bulan - Ubah total_amount menjadi total_price
            $monthlyPurchases = Purchase::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('total_price');
            $purchasesData->push($monthlyPurchases);
        }

        return view('dashboard', [
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'chartLabels' => $months,
            'chartSalesData' => $salesData,
            'chartPurchasesData' => $purchasesData
        ]);
    }
}
