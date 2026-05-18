<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Support\Str;

/**
 * [CORE] Mesin jawaban otomatis berbasis data real — memproses pertanyaan natural language
 * dari pengguna dan memetakannya ke query database yang relevan.
 */
class ChatbotService
{
    public function __construct(
        protected InsightService $insightService
    ) {}

    // =========================================================
    // INTENT MAP — [CORE] Daftar intent yang bisa di-extend
    // =========================================================

    /**
     * [CORE] Peta intent: keyword → nama method handler.
     * Tambahkan entri baru di sini untuk memperluas kemampuan chatbot
     * tanpa mengubah logika utama matchIntent().
     *
     * Format: 'keyword_dalam_pertanyaan' => 'namaMethodHandler'
     *
     * @var array<string, string>
     */
    protected array $intentMap = [
        // Penurunan & performa produk
        'penurunan'          => 'handleDecliningProducts',
        'menurun'            => 'handleDecliningProducts',
        'turun'              => 'handleDecliningProducts',
        'slow moving'        => 'handleDecliningProducts',

        // Stok rendah
        'stok rendah'        => 'handleLowStock',
        'stok menipis'       => 'handleLowStock',
        'stok habis'         => 'handleLowStock',
        'kehabisan stok'     => 'handleLowStock',
        'stock rendah'       => 'handleLowStock',

        // Pengeluaran
        'pengeluaran'        => 'handleMonthlyExpenses',
        'biaya'              => 'handleMonthlyExpenses',
        'expense'            => 'handleMonthlyExpenses',
        'operasional'        => 'handleMonthlyExpenses',

        // Produk terlaris
        'terlaris'           => 'handleTopSellingProducts',
        'paling laku'        => 'handleTopSellingProducts',
        'best seller'        => 'handleTopSellingProducts',
        'produk populer'     => 'handleTopSellingProducts',

        // Pendapatan & omzet
        'pendapatan'         => 'handleTotalRevenue',
        'omzet'              => 'handleTotalRevenue',
        'pemasukan'          => 'handleTotalRevenue',
        'penjualan bulan'    => 'handleTotalRevenue',
        'total penjualan'    => 'handleTotalRevenue',

        // Profit/laba
        'laba'               => 'handleProfitLoss',
        'profit'             => 'handleProfitLoss',
        'untung'             => 'handleProfitLoss',
        'rugi'               => 'handleProfitLoss',
        'keuntungan'         => 'handleProfitLoss',
    ];

    // =========================================================
    // ENTRY POINT
    // =========================================================

    /**
     * [CORE] Proses pertanyaan dari user, cocokkan dengan intent, lalu eksekusi handler.
     *
     * @param  string $question Pertanyaan dalam bahasa natural (Bahasa Indonesia)
     * @param  int    $outletId ID outlet untuk konteks data
     * @return array  { question, answer, data, intent }
     */
    public function ask(string $question, int $outletId): array
    {
        $normalizedQuestion = Str::lower($question);

        // [CORE] Intent matching — cek setiap keyword dalam intentMap
        $matchedIntent  = null;
        $matchedHandler = null;

        foreach ($this->intentMap as $keyword => $handler) {
            if (Str::contains($normalizedQuestion, $keyword)) {
                $matchedIntent  = $keyword;
                $matchedHandler = $handler;
                break; // Ambil intent pertama yang cocok
            }
        }

        // Tidak ada intent yang cocok
        if (! $matchedHandler || ! method_exists($this, $matchedHandler)) {
            return [
                'question' => $question,
                'answer'   => 'Maaf, saya belum bisa menjawab pertanyaan tersebut. Coba tanyakan tentang: produk terlaris, stok rendah, pengeluaran bulan ini, atau pendapatan.',
                'data'     => [],
                'intent'   => null,
            ];
        }

        // [CORE] Panggil handler yang sesuai dengan intent
        return $this->{$matchedHandler}($question, $outletId, $matchedIntent);
    }

    // =========================================================
    // TEMPLATE QUESTIONS — untuk ditampilkan sebagai card di frontend
    // =========================================================

    /**
     * Kembalikan daftar pertanyaan template yang bisa diklik langsung oleh user.
     *
     * @return array<int, array{question: string, category: string, icon: string}>
     */
    public function getTemplateQuestions(): array
    {
        return [
            [
                'question' => 'Produk apa yang sedang mengalami penurunan penjualan?',
                'category' => 'Analisis Produk',
                'icon'     => 'trending-down',
            ],
            [
                'question' => 'Produk apa yang paling laku bulan ini?',
                'category' => 'Analisis Produk',
                'icon'     => 'star',
            ],
            [
                'question' => 'Produk mana yang stok rendah atau menipis?',
                'category' => 'Manajemen Stok',
                'icon'     => 'package',
            ],
            [
                'question' => 'Berapa total pengeluaran bulan ini?',
                'category' => 'Keuangan',
                'icon'     => 'credit-card',
            ],
            [
                'question' => 'Berapa total pendapatan bulan ini?',
                'category' => 'Keuangan',
                'icon'     => 'dollar-sign',
            ],
            [
                'question' => 'Apakah usaha saya untung atau rugi bulan ini?',
                'category' => 'Keuangan',
                'icon'     => 'bar-chart',
            ],
        ];
    }

    // =========================================================
    // INTENT HANDLERS — dipanggil secara dinamis dari ask()
    // =========================================================

    /**
     * [CORE] Handler: Produk mengalami penurunan penjualan
     */
    protected function handleDecliningProducts(string $question, int $outletId, string $intent): array
    {
        $data = $this->insightService->getDecliningProducts($outletId);

        if ($data->isEmpty()) {
            $answer = 'Semua produk Anda menunjukkan performa yang stabil atau meningkat dibanding bulan lalu. Bagus!';
        } else {
            $top = $data->first();
            $answer = "Terdapat {$data->count()} produk yang mengalami penurunan penjualan. Produk dengan penurunan terbesar adalah \"{$top['name']}\" turun {$top['decline_percent']}% dibanding bulan lalu ({$top['last_qty']} → {$top['current_qty']} unit).";
        }

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => $data->toArray(),
            'intent'   => $intent,
        ];
    }

    /**
     * [CORE] Handler: Produk dengan stok rendah
     */
    protected function handleLowStock(string $question, int $outletId, string $intent): array
    {
        $data = $this->insightService->generateStockInsight($outletId);

        $criticalCount = count($data['critical']);
        $warningCount  = count($data['warning']);

        if ($data['total_low_stock'] === 0) {
            $answer = 'Semua produk memiliki stok yang aman. Tidak ada produk di bawah batas minimum.';
        } else {
            $answer = "Terdapat {$data['total_low_stock']} produk yang perlu perhatian: {$criticalCount} produk habis stok dan {$warningCount} produk mendekati batas minimum. Segera lakukan restock.";
        }

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => $data,
            'intent'   => $intent,
        ];
    }

    /**
     * [CORE] Handler: Total pengeluaran bulan berjalan
     */
    protected function handleMonthlyExpenses(string $question, int $outletId, string $intent): array
    {
        // [CORE] Query langsung ke tabel expenses untuk bulan berjalan
        $expenses = Expense::byOutlet($outletId)
            ->thisMonth()
            ->with('expenseCategory')
            ->get();

        $total = $expenses->sum('amount');

        // Rekap per kategori
        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($group) {
            return [
                'category' => $group->first()->expenseCategory?->name ?? 'Tanpa Kategori',
                'total'    => $group->sum('amount'),
                'count'    => $group->count(),
            ];
        })->values();

        $month  = now()->translatedFormat('F Y');
        $answer = "Total pengeluaran bulan {$month} adalah Rp " . number_format($total, 0, ',', '.') . " dari {$expenses->count()} transaksi pengeluaran.";

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => [
                'total'       => $total,
                'count'       => $expenses->count(),
                'by_category' => $byCategory,
            ],
            'intent'   => $intent,
        ];
    }

    /**
     * [CORE] Handler: Produk terlaris
     */
    protected function handleTopSellingProducts(string $question, int $outletId, string $intent): array
    {
        $data = $this->insightService->getTopSellingProducts($outletId, 5);

        if ($data->isEmpty()) {
            $answer = 'Belum ada data penjualan untuk bulan ini.';
        } else {
            $top    = $data->first();
            $answer = "Produk terlaris bulan ini adalah \"{$top->name}\" dengan total {$top->total_sold} unit terjual (pendapatan Rp " . number_format($top->total_revenue, 0, ',', '.') . ").";
        }

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => $data->toArray(),
            'intent'   => $intent,
        ];
    }

    /**
     * [CORE] Handler: Total pendapatan bulan berjalan
     */
    protected function handleTotalRevenue(string $question, int $outletId, string $intent): array
    {
        // [CORE] Agregasi langsung dari tabel transactions bulan ini
        $total = Transaction::byOutlet($outletId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        $count = Transaction::byOutlet($outletId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $month  = now()->translatedFormat('F Y');
        $answer = "Total pendapatan bulan {$month} adalah Rp " . number_format($total, 0, ',', '.') . " dari {$count} transaksi penjualan.";

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => [
                'total_revenue'       => $total,
                'transaction_count'   => $count,
                'average_transaction' => $count > 0 ? round($total / $count, 2) : 0,
            ],
            'intent'   => $intent,
        ];
    }

    /**
     * [CORE] Handler: Profit/Loss bulan berjalan
     */
    protected function handleProfitLoss(string $question, int $outletId, string $intent): array
    {
        $period = now()->format('Y-m');
        $data   = $this->insightService->generateFinancialInsight($outletId, $period);

        $netProfit = $data['net_profit'];
        $status    = $data['status'];

        if ($status === 'profit') {
            $answer = "Usaha Anda sedang UNTUNG! Laba bersih bulan ini Rp " . number_format($netProfit, 0, ',', '.') . " dengan margin {$data['profit_margin']}%. Pertahankan performa ini!";
        } else {
            $answer = "Usaha Anda mengalami RUGI bulan ini sebesar Rp " . number_format(abs($netProfit), 0, ',', '.') . ". Pendapatan: Rp " . number_format($data['total_revenue'], 0, ',', '.') . ", Pengeluaran total: Rp " . number_format($data['total_expenses'], 0, ',', '.') . ". Segera evaluasi pengeluaran!";
        }

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => $data,
            'intent'   => $intent,
        ];
    }
}
