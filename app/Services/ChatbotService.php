<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * [CORE] Mesin jawaban otomatis berbasis data real — memproses pertanyaan natural language
 * dari pengguna dan memetakannya ke query database yang relevan.
 */
class ChatbotService
{
    /**
     * [CORE] Daftar model Gemini yang dicoba secara berurutan (fallback chain).
     * Urutan: dari yang paling hemat quota → ke yang lebih powerful.
     * Update daftar ini jika Google merilis/mempensiunkan model baru.
     *
     * Status per Mei 2026:
     *  - gemini-2.0-flash      → RETIRED (pensiun 3 Maret 2026), JANGAN dipakai
     *  - gemini-1.5-flash      → DEPRECATED, JANGAN dipakai
     *  - gemini-2.5-flash-lite → AKTIF, free tier 15 RPM / 1.000 RPD ✅
     *  - gemini-2.5-flash      → AKTIF, free tier 10 RPM / 250 RPD ✅
     *
     * @var array<int, string>
     */
    protected array $geminiModels = [
        'gemini-2.5-flash-lite', // Prioritas utama: quota paling besar di free tier
        'gemini-2.5-flash',      // Fallback: lebih powerful, quota lebih kecil
    ];

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
        // Sapaan / greeting
        'halo'               => 'handleGreeting',
        'hai'                => 'handleGreeting',
        'hi'                 => 'handleGreeting',
        'hello'              => 'handleGreeting',
        'selamat pagi'       => 'handleGreeting',
        'selamat siang'      => 'handleGreeting',
        'selamat sore'       => 'handleGreeting',
        'apa kabar'          => 'handleGreeting',
        'siapa kamu'         => 'handleGreeting',
        'kamu siapa'         => 'handleGreeting',
        'bantuan'            => 'handleGreeting',
        'bantu'              => 'handleGreeting',
        'help'               => 'handleGreeting',

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
        $apiKey = config('services.gemini.key');

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

        // 1. Kasus A: Ada intent lokal yang cocok
        if ($matchedHandler && method_exists($this, $matchedHandler)) {
            $localResult = $this->{$matchedHandler}($question, $outletId, $matchedIntent);

            // Jika API Key tersedia, percantik jawaban lokal tersebut menggunakan Gemini
            if ($apiKey) {
                $beautifiedAnswer = $this->askGeminiToBeautify($localResult['answer'], $question, $apiKey);
                $localResult['answer'] = $beautifiedAnswer;
            }

            return $localResult;
        }

        // 2. Kasus B: Tidak ada intent lokal yang cocok, tetapi API Key Gemini tersedia (Fallback AI Cerdas)
        if ($apiKey) {
            $aiAnswer = $this->askGeminiWithContext($question, $outletId, $apiKey);

            return [
                'question' => $question,
                'answer'   => $aiAnswer,
                'data'     => [],
                'intent'   => 'ai_fallback',
            ];
        }

        // 3. Kasus C: Tidak ada intent lokal yang cocok & tidak ada API Key Gemini (Offline / Fallback Default)
        return [
            'question' => $question,
            'answer'   => 'Maaf, saya belum bisa menjawab pertanyaan tersebut. Coba tanyakan tentang: produk terlaris, stok rendah, pengeluaran bulan ini, atau pendapatan.',
            'data'     => [],
            'intent'   => null,
        ];
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
     * [CORE] Handler: Sapaan / greeting dari user
     */
    protected function handleGreeting(string $question, int $outletId, string $intent): array
    {
        $jam = (int) now()->format('H');
        if ($jam >= 5 && $jam < 12) {
            $waktu = 'pagi';
        } elseif ($jam >= 12 && $jam < 15) {
            $waktu = 'siang';
        } elseif ($jam >= 15 && $jam < 18) {
            $waktu = 'sore';
        } else {
            $waktu = 'malam';
        }

        $answer = "Halo! Selamat {$waktu}! 👋 Saya adalah asisten bisnis TechneFest. Saya siap membantu Anda menganalisis data outlet secara real-time.\n\nBerikut hal yang bisa saya bantu:\n• 📦 Produk terlaris atau mengalami penurunan\n• ⚠️ Stok produk yang menipis atau habis\n• 💰 Total pendapatan & pengeluaran bulan ini\n• 📊 Analisis untung/rugi usaha Anda\n\nSilakan tanyakan sesuatu atau klik kartu pertanyaan di bawah!";

        return [
            'question' => $question,
            'answer'   => $answer,
            'data'     => [],
            'intent'   => $intent,
        ];
    }

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

    // =========================================================
    // GEMINI AI INTEGRATION METHODS
    // =========================================================

    /**
     * [CORE] Kirim prompt ke Gemini API dengan fallback otomatis antar model.
     *
     * Mencoba model satu per satu sesuai urutan $geminiModels:
     *  - Sukses       → langsung kembalikan teks jawaban
     *  - 429 (quota)  → coba model berikutnya
     *  - Error lain   → hentikan, kembalikan null
     *
     * Return values khusus:
     *  - '__QUOTA_EXCEEDED__' → semua model kehabisan quota
     *  - null                 → error teknis (timeout, koneksi, dsb)
     *
     * @param  string $prompt Teks instruksi untuk AI
     * @param  string $apiKey Kunci API Gemini
     * @return string|null
     */
    public function askGemini(string $prompt, string $apiKey): ?string
    {
        $lastStatus = null;

        foreach ($this->geminiModels as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::timeout(8)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    return $response->json('candidates.0.content.parts.0.text');
                }

                $lastStatus = $response->status();

                if ($lastStatus === 429) {
                    // Quota model ini habis — coba model berikutnya
                    continue;
                }

                // Error selain 429 (misalnya 400, 403, 500) — tidak perlu retry
                return null;

            } catch (\Exception $e) {
                // Exception jaringan (timeout, DNS gagal, dsb) — hentikan loop
                return null;
            }
        }

        // Semua model sudah dicoba, semuanya 429
        if ($lastStatus === 429) {
            return '__QUOTA_EXCEEDED__';
        }

        return null;
    }

    /**
     * Fallback cerdas: Tanya Gemini dengan konteks ringkasan data outlet (RAG).
     *
     * @param  string $question Pertanyaan user
     * @param  int    $outletId ID outlet terkait
     * @param  string $apiKey Kunci API Gemini
     * @return string
     */
    public function askGeminiWithContext(string $question, int $outletId, string $apiKey): string
    {
        // 1. Ambil ringkasan statistik toko dari database
        $totalProducts   = Product::byOutlet($outletId)->count();
        $lowStockCount   = Product::byOutlet($outletId)->lowStock()->count();
        $monthlyRevenue  = Transaction::byOutlet($outletId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        $monthlyExpenses = Expense::byOutlet($outletId)
            ->thisMonth()
            ->sum('amount');

        $monthName = now()->translatedFormat('F Y');

        // 2. Susun System Prompt / Context untuk memberikan intelijen bisnis
        $prompt = "Anda adalah asisten AI bisnis pintar bernama TechneFest. Anda membantu pemilik toko UMKM menganalisis bisnis mereka berdasarkan ringkasan data outlet real-time berikut ini:
- Total produk di toko saat ini: {$totalProducts} barang
- Jumlah produk dengan stok rendah/kritis (perlu restock segera): {$lowStockCount} barang
- Total omzet/pendapatan kotor bulan berjalan ({$monthName}): Rp " . number_format($monthlyRevenue, 0, ',', '.') . "
- Total biaya/pengeluaran operasional bulan berjalan ({$monthName}): Rp " . number_format($monthlyExpenses, 0, ',', '.') . "

Pertanyaan Pengguna: \"{$question}\"

Jawablah pertanyaan tersebut secara solutif, ramah, profesional, dan ringkas dalam Bahasa Indonesia. Fokuskan jawaban Anda untuk membantu pemilik bisnis memahami situasi keuangan atau stoknya. Jika ditanya mengenai tips bisnis atau saran operasional berdasarkan data di atas, berikan masukan yang logis dan membangun.";

        // 3. Panggil Gemini dengan fallback otomatis
        $answer = $this->askGemini($prompt, $apiKey);

        if ($answer === '__QUOTA_EXCEEDED__') {
            return 'Maaf, kuota Gemini API sedang habis untuk hari ini. Namun Anda tetap bisa menanyakan data bisnis spesifik seperti: produk terlaris, stok rendah, pengeluaran, pendapatan, atau untung/rugi bulan ini.';
        }

        return $answer ?? 'Maaf, saya tidak dapat terhubung ke server kecerdasan buatan saat ini. Cek koneksi internet atau coba beberapa saat lagi.';
    }

    /**
     * Mempercantik teks jawaban lokal agar lebih alami menggunakan Gemini.
     *
     * @param  string $localAnswer Jawaban default dari handler lokal
     * @param  string $question Pertanyaan user
     * @param  string $apiKey Kunci API Gemini
     * @return string
     */
    public function askGeminiToBeautify(string $localAnswer, string $question, string $apiKey): string
    {
        $prompt = "Anda adalah asisten AI bisnis pintar bernama TechneFest. Tugas Anda adalah mempercantik teks jawaban bisnis agar terdengar lebih luwes, ramah, dan profesional untuk pemilik toko UMKM dalam Bahasa Indonesia.

Pertanyaan dari Pemilik Toko: \"{$question}\"
Jawaban Data Mentah: \"{$localAnswer}\"

Aturan penting:
1. Jangan mengubah atau memanipulasi angka, statistik, atau fakta data asli di dalam jawaban mentah.
2. Buatlah respons yang mengalir secara alami dan berikan sedikit kalimat penyemangat/rekomendasi bisnis singkat yang relevan.
3. Jawablah langsung ke inti penjelasan dengan santun.";

        $beautifiedAnswer = $this->askGemini($prompt, $apiKey);

        // Jika quota habis atau gagal, kembalikan jawaban lokal apa adanya
        if ($beautifiedAnswer === null || $beautifiedAnswer === '__QUOTA_EXCEEDED__') {
            return $localAnswer;
        }

        return $beautifiedAnswer;
    }
}