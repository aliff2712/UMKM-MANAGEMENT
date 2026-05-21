<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * TransactionWebController — transaksi penjualan via Blade (kasir/POS).
 */
class TransactionWebController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * GET /transactions
     * Riwayat transaksi dengan filter tanggal & metode bayar.
     */
    public function index(Request $request)
    {
        $outletId = auth()->user()->outlet_id;

        $query = Transaction::byOutlet($outletId)
            ->with('user:id,name', 'items.product:id,name')
            ->latest();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        $transactions   = $query->paginate(20)->withQueryString();
        $totalRevenue   = $query->sum('total_amount');
        $filters        = $request->only(['start_date', 'end_date', 'payment_method']);

        return view('web.transactions.index', compact(
            'transactions',
            'totalRevenue',
            'filters'
        ));
    }

    /**
     * GET /transactions/create
     * Halaman kasir / Point of Sale.
     */
    public function create()
    {
        $outletId = auth()->user()->outlet_id;
        $products = Product::byOutlet($outletId)
            ->active()
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'selling_price', 'stock_qty', 'unit', 'category_id', 'image_path']);

        return view('web.transactions.create', compact('products'));
    }

    /**
     * [CORE] POST /transactions
     * Proses transaksi baru secara atomic.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->createTransaction(
                data: array_merge($request->validated(), [
                    'user_id' => auth()->id(),
                ]),
                items: $request->items
            );

            return redirect()
                ->route('transactions.show', $transaction->id)
                ->with('success', "Transaksi berhasil! Invoice: {$transaction->invoice_number}");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * GET /transactions/{id}
     * Struk / detail transaksi.
     */
    public function show(int $id)
    {
        $transaction = Transaction::with([
            'outlet:id,name,address,phone',
            'user:id,name',
            'items.product:id,name,sku,unit',
        ])->findOrFail($id);

        return view('web.transactions.show', compact('transaction'));
    }
}
