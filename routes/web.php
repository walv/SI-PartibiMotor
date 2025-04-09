<?php
//buat komentar agar tidak lupa route mana saja yang belum
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

// Rute autentikasi
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute registrasi (hanya untuk admin)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Rute yang memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    
});

// untuk membatasi akses berdasarkan peran
 // Rute yang hanya bisa diakses oleh admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Rute yang hanya bisa diakses oleh admin
});
// Rute yang hanya bisa diakses oleh kasir
Route::middleware(['auth', 'role:kasir'])->group(function () {
    // Rute yang hanya bisa diakses oleh kasir
});
