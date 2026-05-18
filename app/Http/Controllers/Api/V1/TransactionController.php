<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TransactionController — resource controller untuk transaksi penjualan.
 * Transaksi bersifat immutable: tidak ada edit/delete setelah dibuat.
 */
class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * GET /api/v1/transactions
     * Daftar transaksi dengan filter outlet, tanggal, dan metode pembayaran.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'      => ['required', 'integer', 'exists:outlets,id'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
            'per_page'       => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Transaction::byOutlet((int) $request->outlet_id)
            ->with('user:id,name', 'items.product:id,name,sku')
            ->latest();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil dimuat.',
            'data'    => $transactions,
        ]);
    }

    /**
     * [CORE] POST /api/v1/transactions
     * Buat transaksi baru secara atomic: validasi stok → buat transaksi → kurangi stok → catat movement.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->createTransaction(
                data: array_merge($request->validated(), [
                    'user_id' => $request->user()->id,
                ]),
                items: $request->items
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat. Invoice: ' . $transaction->invoice_number,
                'data'    => $transaction,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }

    /**
     * GET /api/v1/transactions/{id}
     * Detail satu transaksi dengan semua item dan informasi produk.
     */
    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with([
            'outlet:id,name',
            'user:id,name,role',
            'items.product:id,name,sku,unit',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil dimuat.',
            'data'    => $transaction,
        ]);
    }
}
