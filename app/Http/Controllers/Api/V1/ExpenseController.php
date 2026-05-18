<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ExpenseController — resource controller untuk pencatatan pengeluaran operasional.
 */
class ExpenseController extends Controller
{
    /**
     * GET /api/v1/expenses
     * Daftar pengeluaran dengan filter outlet, kategori, dan rentang tanggal.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'           => ['required', 'integer', 'exists:outlets,id'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page'            => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Expense::byOutlet((int) $request->outlet_id)
            ->with('expenseCategory:id,name', 'user:id,name')
            ->latest('expense_date');

        if ($request->filled('expense_category_id')) {
            $query->byCategory((int) $request->expense_category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        $expenses = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil dimuat.',
            'data'    => $expenses,
        ]);
    }

    /**
     * POST /api/v1/expenses
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle upload foto nota/struk
        if ($request->hasFile('receipt_image')) {
            $data['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . $request->outlet_id, 'public');
        }

        $data['user_id'] = $request->user()->id;
        $expense = Expense::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dicatat.',
            'data'    => $expense->load('expenseCategory', 'user:id,name'),
        ], 201);
    }

    /**
     * GET /api/v1/expenses/{id}
     */
    public function show(int $id): JsonResponse
    {
        $expense = Expense::with(['expenseCategory', 'outlet:id,name', 'user:id,name'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pengeluaran berhasil dimuat.',
            'data'    => $expense,
        ]);
    }

    /**
     * PUT /api/v1/expenses/{id}
     */
    public function update(StoreExpenseRequest $request, int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);
        $data    = $request->validated();

        if ($request->hasFile('receipt_image')) {
            // Hapus gambar lama jika ada
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $data['receipt_image'] = $request->file('receipt_image')
                ->store('receipts/' . $expense->outlet_id, 'public');
        }

        $expense->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diperbarui.',
            'data'    => $expense->fresh('expenseCategory'),
        ]);
    }

    /**
     * DELETE /api/v1/expenses/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);

        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus.',
            'data'    => null,
        ]);
    }
}
