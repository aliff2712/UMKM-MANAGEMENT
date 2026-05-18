<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * TransactionService — mengelola pembuatan transaksi penjualan secara atomic.
 */
class TransactionService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    // =========================================================
    // CREATE TRANSACTION
    // =========================================================

    /**
     * [CORE] Buat transaksi penjualan secara ATOMIC dalam satu DB transaction.
     * Mencakup:
     * 1. Generate invoice number unik
     * 2. Buat record transactions
     * 3. Buat semua transaction_items (snapshot harga)
     * 4. Kurangi stock_qty setiap produk yang terjual
     * 5. Catat stock_movements (type: 'out') untuk audit trail
     *
     * @param  array $data  Data header transaksi (outlet_id, user_id, dll)
     * @param  array $items Array item: [['product_id'=>1, 'qty'=>2], ...]
     * @return \App\Models\Transaction
     *
     * @throws \Exception jika stok tidak mencukupi
     */
    public function createTransaction(array $data, array $items): Transaction
    {
        // [CORE] Semua operasi dibungkus dalam satu database transaction
        // untuk memastikan atomicity — jika satu langkah gagal, semua di-rollback
        return DB::transaction(function () use ($data, $items) {

            // Langkah 1: Validasi stok sebelum memulai
            $this->validateStock($items);

            // Langkah 2: Generate invoice number yang unik
            $invoiceNumber = $this->generateInvoiceNumber($data['outlet_id']);

            // Langkah 3: Hitung total amount dari items
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                /** @var Product $product */
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $subtotal = $product->selling_price * $item['qty'];
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id'     => $product->id,
                    'qty'            => $item['qty'],
                    'unit_price'     => $product->selling_price,    // SNAPSHOT harga jual
                    'purchase_price' => $product->purchase_price,   // SNAPSHOT HPP
                    'subtotal'       => $subtotal,
                ];
            }

            $discountAmount = $data['discount_amount'] ?? 0;
            $finalTotal     = $totalAmount - $discountAmount;
            $paidAmount     = $data['paid_amount'] ?? $finalTotal;
            $changeAmount   = max(0, $paidAmount - $finalTotal);

            // Langkah 4: Buat record transaksi
            /** @var Transaction $transaction */
            $transaction = Transaction::create([
                'outlet_id'       => $data['outlet_id'],
                'user_id'         => $data['user_id'],
                'invoice_number'  => $invoiceNumber,
                'total_amount'    => $finalTotal,
                'discount_amount' => $discountAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'note'            => $data['note'] ?? null,
            ]);

            // Langkah 5: Buat transaction_items + update stok + catat movement
            foreach ($preparedItems as $itemData) {
                // Buat item transaksi
                TransactionItem::create(array_merge($itemData, [
                    'transaction_id' => $transaction->id,
                ]));

                // [CORE] Kurangi stok & catat stock_movement (delegasi ke StockService)
                $this->stockService->recordMovement(
                    productId: $itemData['product_id'],
                    userId:    $data['user_id'],
                    type:      'out',
                    qty:       $itemData['qty'],
                    note:      "Penjualan - Invoice: {$invoiceNumber}"
                );
            }

            // Return transaksi dengan relasi yang sudah dimuat
            return $transaction->load('items.product', 'user');
        });
    }

    // =========================================================
    // GENERATE INVOICE NUMBER
    // =========================================================

    /**
     * [CORE] Generate invoice number unik dengan format: INV/YYYYMMDD/XXXX
     * Contoh: INV/20250518/0001
     * Nomor urut di-reset setiap hari dan per-outlet.
     *
     * @param  int $outletId
     * @return string
     */
    public function generateInvoiceNumber(int $outletId): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV/{$date}/";

        // [CORE] Ambil nomor urut terakhir hari ini untuk outlet ini, dengan lock untuk menghindari race condition
        $lastInvoice = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($lastInvoice) {
            // Ambil bagian nomor urut (4 digit terakhir) dan increment
            $lastSequence = (int) substr($lastInvoice, -4);
            $newSequence  = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * [CORE] Validasi ketersediaan stok untuk semua item sebelum transaksi dimulai.
     * Melempar exception jika ada stok yang tidak mencukupi.
     *
     * @param  array $items
     * @throws \Exception
     */
    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                throw new \Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
            }

            if (! $product->is_active) {
                throw new \Exception("Produk \"{$product->name}\" tidak aktif dan tidak bisa dijual.");
            }

            if ($product->stock_qty < $item['qty']) {
                throw new \Exception(
                    "Stok \"{$product->name}\" tidak mencukupi. " .
                    "Tersedia: {$product->stock_qty}, Diminta: {$item['qty']}."
                );
            }
        }
    }
}
