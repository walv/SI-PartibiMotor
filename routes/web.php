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
// Rute Registrasi (Hanya untuk admin)
// ========================
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

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

    //==========================
    // manajemen Jasa
    //==========================
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index'); // Daftar jasa
        Route::get('/create', [ServiceController::class, 'create'])->name('create'); // Form tambah jasa
        Route::post('/', [ServiceController::class, 'store'])->name('store'); // Simpan jasa baru
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit'); // Form edit jasa
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update'); // Update jasa
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy'); // Hapus jasa
    });
    // ========================
    // Manajemen Penjualan
    // ========================
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

    // ========================
    // Peramalan / Forecast
    // ========================
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
    Route::get('/forecast/ses', [ForecastController::class, 'ses'])->name('forecast.ses'); // Untuk SES
    Route::post('/forecast/calculate', [ForecastController::class, 'calculate'])->name('forecast.calculate');
    Route::get('/forecast/result', [ForecastController::class, 'result'])->name('forecast.result'); // Jika diperlukan
});

// ========================
// Rute Berdasarkan Role
// ========================

// Hanya bisa diakses oleh admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Kamu bisa tambah rute khusus admin di sini
});

// Hanya bisa diakses oleh kasir
Route::middleware(['auth', 'role:kasir'])->group(function () {
    // Kamu bisa tambah rute khusus kasir di sini
});
