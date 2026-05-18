<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * ProductWebController — manajemen produk via Blade.
 * Reuse FormRequest yang sama dengan API controller.
 */
class ProductWebController extends Controller
{
    /**
     * GET /products
     * Daftar produk dengan filter.
     */
    public function index(Request $request)
    {
        $outletId  = auth()->user()->outlet_id;
        $categories = Category::withActiveProducts()->orderBy('name')->get();

        $query = Product::byOutlet($outletId)->with('category');

        if ($request->filled('category_id')) {
            $query->byCategory((int) $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        // [CORE] Filter low stock — tampilkan di halaman produk
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                                       ->orWhere('sku', 'like', "%{$search}%"));
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $filters    = $request->only(['category_id', 'is_active', 'low_stock', 'search']);

        return view('web.products.index', compact('products', 'categories', 'filters'));
    }

    /**
     * GET /products/create
     * Form tambah produk baru.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $outletId   = auth()->user()->outlet_id;

        return view('web.products.create', compact('categories', 'outletId'));
    }

    /**
     * POST /products
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', "Produk \"{$product->name}\" berhasil ditambahkan.");
    }

    /**
     * GET /products/{id}
     * Detail produk + riwayat pergerakan stok.
     */
    public function show(int $id)
    {
        $product        = Product::with(['category', 'outlet:id,name'])->findOrFail($id);
        $stockMovements = $product->stockMovements()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('web.products.show', compact('product', 'stockMovements'));
    }

    /**
     * GET /products/{id}/edit
     */
    public function edit(int $id)
    {
        $product    = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('web.products.edit', compact('product', 'categories'));
    }

    /**
     * PUT /products/{id}
     */
    public function update(StoreProductRequest $request, int $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', "Produk \"{$product->name}\" berhasil diperbarui.");
    }

    /**
     * DELETE /products/{id} — soft deactivate
     */
    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => false]);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil dinonaktifkan.");
    }
}
