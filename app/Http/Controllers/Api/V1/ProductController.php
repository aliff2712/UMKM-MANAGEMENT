<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductController — full resource controller untuk manajemen produk.
 */
class ProductController extends Controller
{
    /**
     * GET /api/v1/products
     * Daftar produk dengan filter: outlet_id, category_id, is_active, low_stock.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'   => ['required', 'integer', 'exists:outlets,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active'   => ['nullable', 'boolean'],
            'low_stock'   => ['nullable', 'boolean'],
            'search'      => ['nullable', 'string', 'max:100'],
            'per_page'    => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Product::byOutlet((int) $request->outlet_id)
            ->with('category:id,name');

        if ($request->filled('category_id')) {
            $query->byCategory((int) $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        // [CORE] Filter produk stok rendah menggunakan scope lowStock
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil dimuat.',
            'data'    => $products,
        ]);
    }

    /**
     * POST /api/v1/products
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => $product->load('category'),
        ], 201);
    }

    /**
     * GET /api/v1/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'outlet:id,name'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil dimuat.',
            'data'    => $product,
        ]);
    }

    /**
     * PUT /api/v1/products/{id}
     */
    public function update(StoreProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => $product->fresh('category'),
        ]);
    }

    /**
     * DELETE /api/v1/products/{id}
     * Soft-delete: nonaktifkan produk, tidak hapus permanen.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dinonaktifkan.',
            'data'    => null,
        ]);
    }
}
