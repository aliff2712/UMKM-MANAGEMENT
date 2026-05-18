<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\InsightLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * [CORE] Engine utama insight UMKM — menganalisis data real dari database
 * untuk memberikan rekomendasi bisnis kepada pemilik usaha.
 */
class InsightService
{
    // =========================================================
    // SALES INSIGHT
    // =========================================================

    /**
     * [CORE] Generate insight penjualan: produk terlaris & produk mengalami penurunan.
     * Menyimpan hasilnya ke tabel insight_logs.
     *
     * @param  int    $outletId  ID outlet yang dianalisis
     * @param  string $period    Format: 'YYYY-MM' (contoh: '2025-05')
     * @return array
     */
    public function generateSalesInsight(int $outletId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $topProducts     = $this->getTopSellingProducts($outletId, 5);
        $decliningProducts = $this->getDecliningProducts($outletId);

        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Simpan insight ke database agar bisa di-render dengan efek typing di frontend
        if ($topProducts->isNotEmpty()) {
            InsightLog::create([
                'outlet_id'    => $outletId,
                'type'         => 'top_product',
                'title'        => 'Produk Terlaris Bulan Ini',
                'message'      => 'Produk terlaris Anda adalah "' . $topProducts->first()->name . '" dengan total penjualan ' . number_format($topProducts->first()->total_sold) . ' unit.',
                'severity'     => 'info',
                'period_start' => $periodStart->toDateString(),
                'period_end'   => $periodEnd->toDateString(),
                'metadata'     => $topProducts->toArray(),
            ]);
        }

        if ($decliningProducts->isNotEmpty()) {
            InsightLog::create([
                'outlet_id'    => $outletId,
                'type'         => 'slow_moving',
                'title'        => 'Produk Mengalami Penurunan Penjualan',
                'message'      => 'Terdapat ' . $decliningProducts->count() . ' produk yang penjualannya menurun dibanding bulan lalu. Pertimbangkan strategi promosi.',
                'severity'     => 'warning',
                'period_start' => $periodStart->toDateString(),
                'period_end'   => $periodEnd->toDateString(),
                'metadata'     => $decliningProducts->toArray(),
            ]);
        }

        return [
            'period'            => $period,
            'top_products'      => $topProducts,
            'declining_products' => $decliningProducts,
        ];
    }

    // =========================================================
    // STOCK INSIGHT
    // =========================================================

    /**
     * [CORE] Generate insight stok: deteksi produk di bawah minimum stok.
     * Juga membuat notifikasi jika stok kritis.
     *
     * @param  int $outletId
     * @return array
     */
    public function generateStockInsight(int $outletId): array
    {
        $lowStockProducts = Product::byOutlet($outletId)
            ->active()
            ->lowStock()
            ->with('category')
            ->get(['id', 'name', 'sku', 'stock_qty', 'stock_minimum', 'category_id']);

        $critical = $lowStockProducts->filter(fn ($p) => $p->stock_qty === 0);
        $warning  = $lowStockProducts->filter(fn ($p) => $p->stock_qty > 0);

        // Simpan insight jika ada produk stok rendah
        if ($lowStockProducts->isNotEmpty()) {
            InsightLog::create([
                'outlet_id'    => $outletId,
                'type'         => 'slow_moving',
                'title'        => 'Peringatan Stok Menipis',
                'message'      => $critical->count() . ' produk kehabisan stok dan ' . $warning->count() . ' produk mendekati batas minimum.',
                'severity'     => $critical->isNotEmpty() ? 'critical' : 'warning',
                'period_start' => now()->toDateString(),
                'period_end'   => now()->toDateString(),
                'metadata'     => $lowStockProducts->toArray(),
            ]);
        }

        return [
            'total_low_stock' => $lowStockProducts->count(),
            'critical'        => $critical->values(),
            'warning'         => $warning->values(),
        ];
    }

    // =========================================================
    // FINANCIAL INSIGHT
    // =========================================================

    /**
     * [CORE] Generate insight keuangan: hitung profit/loss dari transaksi vs pengeluaran.
     *
     * @param  int    $outletId
     * @param  string $period   Format: 'YYYY-MM'
     * @return array
     */
    public function generateFinancialInsight(int $outletId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $totalRevenue = Transaction::byOutlet($outletId)
            ->inMonth($month, $year)
            ->sum('total_amount');

        $totalExpenses = Expense::byOutlet($outletId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // [CORE] Hitung HPP total dari transaction_items untuk profit bruto
        $totalCOGS = TransactionItem::whereHas('transaction', function ($q) use ($outletId, $year, $month) {
                $q->where('outlet_id', $outletId)
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
            })
            ->selectRaw('SUM(purchase_price * qty) as total_cogs')
            ->value('total_cogs') ?? 0;

        $grossProfit  = $totalRevenue - $totalCOGS;
        $netProfit    = $grossProfit - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;
        $cashflowRatio = $totalExpenses > 0 ? round($totalRevenue / $totalExpenses, 2) : null;

        $severity = 'info';
        $message  = "Bulan $period: Pendapatan Rp " . number_format($totalRevenue) . ", Pengeluaran Rp " . number_format($totalExpenses) . ", Laba Bersih Rp " . number_format($netProfit);

        if ($netProfit < 0) {
            $severity = 'critical';
            $message  = "PERHATIAN: Bulan $period mengalami kerugian sebesar Rp " . number_format(abs($netProfit)) . ". Segera tinjau pengeluaran operasional.";
        } elseif ($profitMargin < 15) {
            $severity = 'warning';
        }

        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        InsightLog::create([
            'outlet_id'    => $outletId,
            'type'         => 'cashflow',
            'title'        => 'Ringkasan Keuangan ' . $period,
            'message'      => $message,
            'severity'     => $severity,
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'metadata'     => [
                'total_revenue'  => $totalRevenue,
                'total_cogs'     => $totalCOGS,
                'gross_profit'   => $grossProfit,
                'total_expenses' => $totalExpenses,
                'net_profit'     => $netProfit,
                'profit_margin'  => $profitMargin,
                'cashflow_ratio' => $cashflowRatio,
            ],
        ]);

        return [
            'period'         => $period,
            'total_revenue'  => $totalRevenue,
            'total_cogs'     => $totalCOGS,
            'gross_profit'   => $grossProfit,
            'total_expenses' => $totalExpenses,
            'net_profit'     => $netProfit,
            'profit_margin'  => $profitMargin,
            'cashflow_ratio' => $cashflowRatio,
            'status'         => $netProfit >= 0 ? 'profit' : 'loss',
        ];
    }

    // =========================================================
    // TOP SELLING PRODUCTS
    // =========================================================

    /**
     * [CORE] Ambil produk dengan total penjualan tertinggi dalam bulan berjalan.
     *
     * @param  int $outletId
     * @param  int $limit    Jumlah produk yang dikembalikan (default 5)
     * @return Collection
     */
    public function getTopSellingProducts(int $outletId, int $limit = 5): Collection
    {
        return TransactionItem::select(
                'transaction_items.product_id',
                'products.name',
                'products.sku',
                'products.unit',
                DB::raw('SUM(transaction_items.qty) as total_sold'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.outlet_id', $outletId)
            ->whereYear('transactions.created_at', now()->year)
            ->whereMonth('transactions.created_at', now()->month)
            ->groupBy('transaction_items.product_id', 'products.name', 'products.sku', 'products.unit')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    // =========================================================
    // DECLINING PRODUCTS
    // =========================================================

    /**
     * [CORE] Bandingkan penjualan bulan ini vs bulan lalu untuk mendeteksi produk yang menurun.
     * Engine inti analisis trend — membandingkan qty terjual per produk antar periode.
     *
     * @param  int $outletId
     * @return Collection  Collection produk dengan data: current_qty, last_qty, decline_percent
     */
    public function getDecliningProducts(int $outletId): Collection
    {
        $currentMonth = now();
        $lastMonth    = now()->subMonth();

        // [CORE] Query agregasi penjualan bulan ini per produk
        $currentSales = TransactionItem::select(
                'transaction_items.product_id',
                DB::raw('SUM(transaction_items.qty) as total_qty')
            )
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.outlet_id', $outletId)
            ->whereYear('transactions.created_at', $currentMonth->year)
            ->whereMonth('transactions.created_at', $currentMonth->month)
            ->groupBy('transaction_items.product_id')
            ->pluck('total_qty', 'product_id');

        // [CORE] Query agregasi penjualan bulan lalu per produk
        $lastSales = TransactionItem::select(
                'transaction_items.product_id',
                DB::raw('SUM(transaction_items.qty) as total_qty')
            )
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.outlet_id', $outletId)
            ->whereYear('transactions.created_at', $lastMonth->year)
            ->whereMonth('transactions.created_at', $lastMonth->month)
            ->groupBy('transaction_items.product_id')
            ->pluck('total_qty', 'product_id');

        // [CORE] Bandingkan: cari produk yang qty bulan ini < bulan lalu
        $declining = collect();

        foreach ($lastSales as $productId => $lastQty) {
            $currentQty = $currentSales->get($productId, 0);

            if ($currentQty < $lastQty) {
                $declinePercent = $lastQty > 0
                    ? round((($lastQty - $currentQty) / $lastQty) * 100, 1)
                    : 0;

                $product = Product::find($productId, ['id', 'name', 'sku', 'selling_price']);

                if ($product) {
                    $declining->push([
                        'product_id'      => $productId,
                        'name'            => $product->name,
                        'sku'             => $product->sku,
                        'current_qty'     => $currentQty,
                        'last_qty'        => $lastQty,
                        'decline_percent' => $declinePercent,
                    ]);
                }
            }
        }

        // Urutkan dari penurunan paling signifikan
        return $declining->sortByDesc('decline_percent')->values();
    }
}
