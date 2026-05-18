<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ExpenseWebController — pencatatan pengeluaran operasional via Blade.
 */
class ExpenseWebController extends Controller
{
    /**
     * GET /expenses
     * Daftar pengeluaran dengan filter.
     */
    public function index(Request $request)
    {
        $outletId   = auth()->user()->outlet_id;
        $categories = ExpenseCategory::orderBy('name')->get();

        $query = Expense::byOutlet($outletId)
            ->with('expenseCategory:id,name', 'user:id,name')
            ->latest('expense_date');

        if ($request->filled('expense_category_id')) {
            $query->byCategory((int) $request->expense_category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        $expenses     = $query->paginate(20)->withQueryString();
        $totalAmount  = $query->sum('amount'); // Total pengeluaran periode yang difilter
        $filters      = $request->only(['expense_category_id', 'start_date', 'end_date']);

        return view('web.expenses.index', compact('expenses', 'categories', 'totalAmount', 'filters'));
    }

    /**
     * GET /expenses/create
     * Form tambah pengeluaran baru.
     */
    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('web.expenses.create', compact('categories'));
    }

    /**
     * POST /expenses
     */
    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('receipt_image')) {
            $data['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . auth()->user()->outlet_id, 'public');
        }

        $data['user_id']    = auth()->id();
        $data['outlet_id']  = auth()->user()->outlet_id;

        Expense::create($data);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /**
     * GET /expenses/{id}/edit
     */
    public function edit(int $id)
    {
        $expense    = Expense::findOrFail($id);
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('web.expenses.edit', compact('expense', 'categories'));
    }

    /**
     * PUT /expenses/{id}
     */
    public function update(StoreExpenseRequest $request, int $id)
    {
        $expense = Expense::findOrFail($id);
        $data    = $request->validated();

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $data['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . $expense->outlet_id, 'public');
        }

        $expense->update($data);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * DELETE /expenses/{id}
     */
    public function destroy(int $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
