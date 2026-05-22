<?php

use App\Http\Controllers\Api\V1\ChatbotController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\InsightController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TechneFest UMKM Management Platform
|--------------------------------------------------------------------------
| Prefix  : /api/v1 (diset di RouteServiceProvider atau bootstrap/app.php)
| Auth    : auth:sanctum untuk semua route kecuali /login
|--------------------------------------------------------------------------
*/

// =========================================================
// PUBLIC ROUTES — tidak butuh autentikasi
// =========================================================

Route::prefix('v1')->group(function () {

    /**
     * POST /api/v1/login
     * Autentikasi user dan generate Sanctum token.
     */
    Route::post('/login', function (Request $request) {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
                'data'    => null,
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                'data'    => null,
            ], 403);
        }

        $token = $user->createToken('technefest_' . $user->role)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => $user->only('id', 'name', 'email', 'role', 'outlet_id'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    });

    // =========================================================
    // PROTECTED ROUTES — wajib auth:sanctum
    // =========================================================

    Route::middleware('auth:sanctum')->group(function () {

        /**
         * GET /api/v1/me — informasi user yang sedang login
         */
        Route::get('/me', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil dimuat.',
                'data'    => $request->user()->load('outlet:id,name'),
            ]);
        });

        /**
         * POST /api/v1/logout
         */
        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
                'data'    => null,
            ]);
        });

        // =========================================================
        // HAK AKSES: OWNER ONLY
        // =========================================================
        Route::middleware(['role:owner'])->group(function () {
            // === [CORE] INSIGHT ROUTES ===
            // Engine analitik utama: penjualan, stok, keuangan
            Route::prefix('insights')->group(function () {
                Route::get('/', [InsightController::class, 'index']);
                Route::get('/sales', [InsightController::class, 'sales']);
                Route::get('/stock', [InsightController::class, 'stock']);
                Route::get('/financial', [InsightController::class, 'financial']);
            });

            // === [CORE] CHATBOT ROUTES ===
            // Mesin tanya-jawab berbasis data real UMKM
            Route::prefix('chatbot')->group(function () {
                Route::get('/questions', [ChatbotController::class, 'questions']); // template card UI
                Route::post('/ask', [ChatbotController::class, 'ask']);            // kirim pertanyaan
            });

            // === PROFIT & LOSS REPORT ===
            Route::get('reports/profit-loss', [ReportController::class, 'profitLoss']); // [CORE] Laba bersih
            Route::get('reports/profit-loss/export', [ReportController::class, 'exportProfitLossReport']); // Export P&L
        });

        // =========================================================
        // HAK AKSES: OWNER & ADMIN
        // =========================================================
        Route::middleware(['role:owner,admin'])->group(function () {
            // === PRODUCT ROUTES ===
            // Full resource: CRUD + filter low_stock, category, is_active
            Route::apiResource('products', ProductController::class);

            // === STOCK MOVEMENT ROUTES ===
            // Riwayat stok & tambah/kurangi stok manual
            Route::prefix('stock-movements')->group(function () {
                Route::get('/', [StockMovementController::class, 'index']);
                Route::post('/', [StockMovementController::class, 'store']);
            });

            // === EXPENSE ROUTES ===
            // Pencatatan pengeluaran operasional + upload nota
            Route::apiResource('expenses', ExpenseController::class);

            // === OPERATIONAL REPORTS ===
            Route::prefix('reports')->group(function () {
                Route::get('/sales', [ReportController::class, 'sales']);
                Route::get('/sales/export', [ReportController::class, 'exportSalesReport']); // Export Sales
                Route::get('/expenses', [ReportController::class, 'expenses']);
                Route::get('/expenses/export', [ReportController::class, 'exportExpenseReport']); // Export Expenses
            });
        });

        // =========================================================
        // HAK AKSES: OWNER, ADMIN, KASIR
        // =========================================================
        Route::middleware(['role:owner,admin,kasir'])->group(function () {
            // === [CORE] TRANSACTION ROUTES ===
            // Transaksi bersifat immutable — hanya index, store, show
            Route::prefix('transactions')->group(function () {
                Route::get('/', [TransactionController::class, 'index']);
                Route::post('/', [TransactionController::class, 'store']);
                Route::get('/{id}', [TransactionController::class, 'show']);
            });
        });

    }); // end auth:sanctum

}); // end v1 prefix
