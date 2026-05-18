<?php

use App\Http\Controllers\Web\ChatbotWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExpenseWebController;
use App\Http\Controllers\Web\InsightWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\StockWebController;
use App\Http\Controllers\Web\TransactionWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — TechneFest UMKM (Blade Templates)
|--------------------------------------------------------------------------
| Semua route di sini dilindungi middleware 'auth' (session-based).
| CSRF protection otomatis aktif via middleware 'web'.
|--------------------------------------------------------------------------
*/

// =========================================================
// HALAMAN PUBLIK
// =========================================================

Route::get('/', fn () => redirect()->route('dashboard'));

// Route Login — dikelola Laravel default (auth scaffold)
// Jika belum ada, jalankan: php artisan make:auth
// Atau buat manual LoginController sendiri.
Route::get('/login', fn () => view('auth.login'))->name('login')->middleware('guest');

// =========================================================
// HALAMAN TERPROTEKSI — wajib login (session)
// =========================================================

Route::middleware(['auth'])->group(function () {

    // === DASHBOARD ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === INSIGHT ROUTES ===
    Route::prefix('insights')->name('insights.')->group(function () {
        Route::get('/',          [InsightWebController::class, 'index'])->name('index');
        Route::get('/sales',     [InsightWebController::class, 'sales'])->name('sales');
        Route::get('/stock',     [InsightWebController::class, 'stock'])->name('stock');
        Route::get('/financial', [InsightWebController::class, 'financial'])->name('financial');
    });

    // === CHATBOT ROUTES ===
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        Route::get('/',    [ChatbotWebController::class, 'index'])->name('index');
        Route::post('/ask', [ChatbotWebController::class, 'ask'])->name('ask');
    });

    // === PRODUCT ROUTES (full resource) ===
    Route::resource('products', ProductWebController::class);

    // === TRANSACTION ROUTES ===
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/',          [TransactionWebController::class, 'index'])->name('index');
        Route::get('/create',    [TransactionWebController::class, 'create'])->name('create');
        Route::post('/',         [TransactionWebController::class, 'store'])->name('store');
        Route::get('/{id}',      [TransactionWebController::class, 'show'])->name('show');
    });

    // === STOCK ROUTES ===
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',           [StockWebController::class, 'index'])->name('index');
        Route::get('/movements',  [StockWebController::class, 'movements'])->name('movements');
        Route::get('/adjust',     [StockWebController::class, 'adjust'])->name('adjust');
        Route::post('/movements', [StockWebController::class, 'storeMovement'])->name('movements.store');
    });

    // === EXPENSE ROUTES (full resource) ===
    Route::resource('expenses', ExpenseWebController::class);

    // === REPORT ROUTES ===
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales',        [ReportWebController::class, 'sales'])->name('sales');
        Route::get('/expenses',     [ReportWebController::class, 'expenses'])->name('expenses');
        Route::get('/profit-loss',  [ReportWebController::class, 'profitLoss'])->name('profit-loss');
    });

}); // end auth middleware
