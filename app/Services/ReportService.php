<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ReportService — menghasilkan laporan bisnis berbasis periode untuk pemilik usaha.
 */
class ReportService
{
    /**
     * Laporan penjualan per periode: rekap transaksi, produk terlaris, revenue per hari.
     *
     * @param  int    $outletId
     * @param  string $startDate  Format: YYYY-MM-DD
     * @param  string $endDate    Format: YYYY-MM-DD
     * @return array
     */
    public function getSalesReport(int $outletId, string $startDate, string $endDate): array
    {
        $transactions = Transaction::byOutlet($outletId)
            ->inPeriod($startDate, $endDate)
            ->with('items.product', 'user:id,name')
            ->get();

        $totalRevenue    = $transactions->sum('total_amount');
        $totalDiscount   = $transactions->sum('discount_amount');
        $totalTransactions = $transactions->count();
        $averageTransaction = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;

        // Rekap per metode pembayaran
        $byPaymentMethod = $transactions->groupBy('payment_method')->map(fn ($group) => [
            'count'  => $group->count(),
            'total'  => $group->sum('total_amount'),
        ]);

        // Rekap penjualan per produk dalam periode
        $productSales = TransactionItem::select(
                'transaction_items.product_id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                DB::raw('SUM((transaction_items.unit_price - transaction_items.purchase_price) * transaction_items.qty) as gross_margin')
            )
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.outlet_id', $outletId)
            ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('transaction_items.product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->get();

        // Revenue per hari
        $dailyRevenue = $transactions->groupBy(fn ($t) => $t->created_at->toDateString())
            ->map(fn ($group) => [
                'date'  => $group->first()->created_at->toDateString(),
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
            ])->values();

        return [
            'period'              => ['start' => $startDate, 'end' => $endDate],
            'summary'             => [
                'total_revenue'       => $totalRevenue,
                'total_discount'      => $totalDiscount,
                'total_transactions'  => $totalTransactions,
                'average_transaction' => $averageTransaction,
            ],
            'by_payment_method'   => $byPaymentMethod,
            'product_sales'       => $productSales,
            'daily_revenue'       => $dailyRevenue,
        ];
    }

    /**
     * Laporan pengeluaran per periode: total, rekap per kategori, tren harian.
     *
     * @param  int    $outletId
     * @param  string $startDate
     * @param  string $endDate
     * @return array
     */
    public function getExpenseReport(int $outletId, string $startDate, string $endDate): array
    {
        $expenses = Expense::byOutlet($outletId)
            ->inPeriod($startDate, $endDate)
            ->with('expenseCategory:id,name', 'user:id,name')
            ->orderBy('expense_date')
            ->get();

        $totalAmount = $expenses->sum('amount');

        // Rekap per kategori pengeluaran
        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($group) {
            return [
                'category_id'   => $group->first()->expense_category_id,
                'category_name' => $group->first()->expenseCategory?->name ?? 'Tanpa Kategori',
                'count'         => $group->count(),
                'total'         => $group->sum('amount'),
            ];
        })->sortByDesc('total')->values();

        // Pengeluaran per hari
        $dailyExpenses = $expenses->groupBy(fn ($e) => $e->expense_date->toDateString())
            ->map(fn ($group) => [
                'date'  => $group->first()->expense_date->toDateString(),
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ])->values();

        return [
            'period'         => ['start' => $startDate, 'end' => $endDate],
            'summary'        => [
                'total_amount'   => $totalAmount,
                'total_records'  => $expenses->count(),
                'average_daily'  => $expenses->isNotEmpty()
                    ? round($totalAmount / max(1, $dailyExpenses->count()), 2)
                    : 0,
            ],
            'by_category'    => $byCategory,
            'daily_expenses' => $dailyExpenses,
        ];
    }

    /**
     * [CORE] Hitung laba bersih (Profit & Loss) per bulan.
     * Formula: Pendapatan - HPP (COGS) - Pengeluaran Operasional = Laba Bersih
     *
     * @param  int    $outletId
     * @param  string $month   Format: 'MM' (contoh: '05')
     * @param  string $year    Format: 'YYYY' (contoh: '2025')
     * @return array
     */
    public function getProfitLossReport(int $outletId, string $month, string $year): array
    {
        // [CORE] 1. Hitung total pendapatan (revenue) dari transaksi
        $totalRevenue = Transaction::byOutlet($outletId)
            ->inMonth($month, $year)
            ->sum('total_amount');

        $totalDiscount = Transaction::byOutlet($outletId)
            ->inMonth($month, $year)
            ->sum('discount_amount');

        // [CORE] 2. Hitung HPP (Cost of Goods Sold) dari snapshot purchase_price di transaction_items
        $totalCOGS = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.outlet_id', $outletId)
            ->whereYear('transactions.created_at', $year)
            ->whereMonth('transactions.created_at', $month)
            ->sum(DB::raw('transaction_items.purchase_price * transaction_items.qty'));

        // [CORE] 3. Hitung total pengeluaran operasional dari tabel expenses
        $totalOperationalExpenses = Expense::byOutlet($outletId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // [CORE] 4. Hitung laba bertahap
        $grossProfit = $totalRevenue - $totalCOGS;         // Laba Kotor (sebelum opex)
        $netProfit   = $grossProfit - $totalOperationalExpenses; // Laba Bersih

        $grossMarginPercent = $totalRevenue > 0
            ? round(($grossProfit / $totalRevenue) * 100, 2)
            : 0;

        $netMarginPercent = $totalRevenue > 0
            ? round(($netProfit / $totalRevenue) * 100, 2)
            : 0;

        // Pengeluaran per kategori untuk breakdown
        $expenseBreakdown = Expense::byOutlet($outletId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->with('expenseCategory:id,name')
            ->get()
            ->groupBy('expense_category_id')
            ->map(fn ($group) => [
                'category' => $group->first()->expenseCategory?->name ?? 'Lainnya',
                'total'    => $group->sum('amount'),
            ])->sortByDesc('total')->values();

        return [
            'period'        => ['month' => $month, 'year' => $year],
            'income'        => [
                'gross_revenue'  => $totalRevenue + $totalDiscount, // Sebelum diskon
                'total_discount' => $totalDiscount,
                'net_revenue'    => $totalRevenue,
            ],
            'cogs'          => $totalCOGS,
            'gross_profit'  => $grossProfit,
            'gross_margin'  => $grossMarginPercent,
            'expenses'      => [
                'total'     => $totalOperationalExpenses,
                'breakdown' => $expenseBreakdown,
            ],
            'net_profit'         => $netProfit,
            'net_margin'         => $netMarginPercent,
            'status'             => $netProfit >= 0 ? 'profit' : 'loss',
        ];
    }
}
