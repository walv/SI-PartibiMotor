<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\ManualImportController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Di sinilah kamu mendefinisikan semua rute aplikasi web.
| File ini dimuat oleh RouteServiceProvider di dalam grup "web".
*/

// ========================
// Rute Awal (redirect ke login)
// ========================
Route::get('/', function () {
    return redirect('/login');
});

// ========================
// Rute Autentikasi
// ========================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// ========================
// Rute Ganti Password
// ========================
Route::get('/change-password', [UserController::class, 'changePassword'])->name('change.password');
Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update.password');

// ========================
// Rute yang Membutuhkan Login
// ========================
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account/show', [App\Http\Controllers\AccountController::class, 'show'])->name('account.show');
    Route::get('/account', [App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::post('/account', [App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
});
Route::middleware(['auth', 'role:admin,pemilik'])->group(function () {
   // Rute Laporan (Hanya untuk Admin pemilik)
// ========================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales'); // Laporan Penjualan
    Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases'); // Laporan Pembelian
    Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory'); // Laporan Stok Barang
    Route::get('/financial', [ReportController::class, 'financial'])->name('financial'); // Laporan Keuangan
    Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('reports.purchases.export');
    Route::get('/financial/export', [ReportController::class, 'exportFinancial'])->name('reports.financial.export');
    Route::get('/inventory/export', [ReportController::class, 'exportInventory'])->name('reports.inventory.export');
     Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
        Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('purchases.export');
        Route::get('/financial/export', [ReportController::class, 'exportFinancial'])->name('financial.export');
        Route::get('/inventory/export', [ReportController::class, 'exportInventory'])->name('inventory.export');
    
});
});

// ========================
// Rute Berdasarkan Role
// ========================
// Hanya bisa diakses oleh admin
Route::middleware(['auth', 'role:admin'])->group(function () {

    //=========================
   // export import
   //=========================
    // Halaman utama untuk export dan import Uji Coba
    Route::get('/sales/exportimport', [ExportImportController::class, 'index'])->name('sales.exportimport');
    // Export Data
    Route::get('/sales/export', [ExportImportController::class, 'export'])->name('sales.export');
    // Import Data
    Route::post('/sales/import', [ExportImportController::class, 'import'])->name('sales.import');
    Route::get('/sales/import/template', [ExportImportController::class, 'downloadTemplate'])->name('sales.import.template');
    Route::get('/sales/import/manual', [ManualImportController::class, 'create'])->name('sales.import.manual');
    Route::post('/sales/import/manual', [ManualImportController::class, 'store'])->name('sales.import.manual.store');
  
    // ========================
    // Manajemen Pembelian
    // ========================
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])->name('purchases.invoice');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // ========================
    // Manajemen Kategori
    // ========================
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // ========================
    // Manajemen Produk
    // ========================
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // ========================
    // Manajemen Jasa
    // ========================
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index'); // Daftar jasa
        Route::get('/create', [ServiceController::class, 'create'])->name('create'); // Form tambah jasa
        Route::post('/', [ServiceController::class, 'store'])->name('store'); // Simpan jasa baru
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit'); // Form edit jasa
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update'); // Update jasa
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy'); // Hapus jasa
    });

    // ========================
    // Peramalan / Forecast
    // ========================
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
    Route::get('/forecast/ses', [ForecastController::class, 'ses'])->name('forecast.ses'); // Untuk SES
    Route::post('/forecast/calculate', [ForecastController::class, 'calculate'])->name('forecast.calculate');
    Route::get('/forecast/result', [ForecastController::class, 'result'])->name('forecast.result'); // Jika diperlukan

     Route::resource('users', UserController::class)->except(['show']);
    
  Route::get('/users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    
    // Route untuk ganti password
    Route::get('/change-password', [UserController::class, 'changePassword'])->name('change.password');
    Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update.password');
   
});


// Hanya bisa diakses oleh admin dan kasir 
    Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::prefix('sales')->group(function () {
        // Urutan dari yang paling spesifik ke umum
        Route::get('/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/', [SaleController::class, 'index'])->name('sales.index');
       
        // Route parameter harus di bawah
        Route::get('/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });
});

