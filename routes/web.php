<?php

use App\Http\Controllers\Web\AuthController;
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
| Web Routes — Umora UMKM (Blade Templates)
|--------------------------------------------------------------------------
| Semua route di sini dilindungi middleware 'auth' (session-based).
| CSRF protection otomatis aktif via middleware 'web'.
|--------------------------------------------------------------------------
*/

// =========================================================
// HALAMAN PUBLIK / GUEST
// =========================================================

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// =========================================================
// HALAMAN TERPROTEKSI — wajib login (session)
// =========================================================

Route::middleware(['auth'])->group(function () {

    // === LOGOUT ===
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // === DASHBOARD (Akses Umum untuk Semua Role Terautentikasi) ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================
    // HAK AKSES: OWNER ONLY
    // =========================================================
    Route::middleware(['role:owner'])->group(function () {
        // === INSIGHT ROUTES ===
        Route::prefix('insights')->name('insights.')->group(function () {
            Route::get('/',          [InsightWebController::class, 'index'])->name('index');
            Route::get('/sales',     [InsightWebController::class, 'sales'])->name('sales');
            Route::get('/stock',     [InsightWebController::class, 'stock'])->name('stock');
            Route::get('/financial', [InsightWebController::class, 'financial'])->name('financial');
        });

        // === CHATBOT ROUTES ===
        Route::prefix('chatbot')->name('chatbot.')->group(function () {
            Route::get('/',     [ChatbotWebController::class, 'index'])->name('index');
            Route::post('/ask', [ChatbotWebController::class, 'ask'])->name('ask');
        });

        // === PROFIT & LOSS REPORT ===
        Route::get('reports/profit-loss', [ReportWebController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::post('reports/profit-loss/export', [ReportWebController::class, 'exportProfitLossReport'])->name('reports.profit-loss.export');
    });

    // =========================================================
    // HAK AKSES: OWNER & ADMIN
    // =========================================================
    Route::middleware(['role:owner,admin'])->group(function () {
        // === PRODUCT ROUTES (full resource) ===
        Route::resource('products', ProductWebController::class);

        // === STOCK ROUTES ===
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/',           [StockWebController::class, 'index'])->name('index');
            Route::get('/movements',  [StockWebController::class, 'movements'])->name('movements');
            Route::get('/adjust',     [StockWebController::class, 'adjust'])->name('adjust');
            Route::post('/movements', [StockWebController::class, 'storeMovement'])->name('movements.store');
        });

        // === EXPENSE ROUTES (full resource) ===
        Route::resource('expenses', ExpenseWebController::class);

        // === OPERATIONAL REPORTS ===
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales',    [ReportWebController::class, 'sales'])->name('sales');
            Route::post('/sales/export', [ReportWebController::class, 'exportSalesReport'])->name('sales.export');
            Route::get('/expenses', [ReportWebController::class, 'expenses'])->name('expenses');
            Route::post('/expenses/export', [ReportWebController::class, 'exportExpenseReport'])->name('expenses.export');
        });
    });

    // =========================================================
    // HAK AKSES: OWNER, ADMIN, KASIR
    // =========================================================
    Route::middleware(['role:owner,admin,kasir'])->group(function () {
        // === TRANSACTION ROUTES ===
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/',          [TransactionWebController::class, 'index'])->name('index');
            Route::get('/create',    [TransactionWebController::class, 'create'])->name('create');
            Route::post('/',         [TransactionWebController::class, 'store'])->name('store');
            Route::get('/{id}',      [TransactionWebController::class, 'show'])->name('show');
        });
    });

}); // end auth middleware
