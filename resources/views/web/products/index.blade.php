@extends('layouts.app')

@section('title', 'Kelola Produk')
@section('page_title', 'Daftar Produk Outlet')

@section('content')

    {{-- ── Filter Card ── --}}
    <div class="glass-card mb-6">
        <form action="{{ route('products.index') }}" method="GET">

            <div class="flex-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <h4 style="font-weight: 700; font-size: 1rem; color: var(--text-main);">Filter Pencarian</h4>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus-circle"></i>
                    <span>Tambah Produk</span>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search">Cari Nama / SKU</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="Ketik nama atau SKU..." value="{{ $filters['search'] ?? '' }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="category_id">Kategori</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; flex-wrap: wrap; gap: 12px;">
                <label class="form-check" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem; color: var(--text-muted); font-weight: 600;">
                    <input type="checkbox" name="low_stock" value="1" {{ ($filters['low_stock'] ?? '') ? 'checked' : '' }}>
                    <span>Tampilkan stok rendah saja</span>
                </label>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter">
                        <i data-lucide="refresh-cw"></i>
                        <span>Reset</span>
                    </a>
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i data-lucide="search"></i>
                        <span>Cari</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Table Card ── --}}
    <div class="glass-card">
        @if($products->isEmpty())
            <div style="text-align: center; padding: 64px 24px; color: var(--text-muted);">
                <i data-lucide="package-x" style="width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.4;"></i>
                <p style="font-size: 0.9rem;">Tidak ada produk ditemukan.<br>Tambahkan produk baru atau sesuaikan filter.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th class="hide-mobile">Kategori</th>
                            <th class="text-right hide-mobile">Harga Beli</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Stok</th>
                            <th class="hide-mobile">Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                            @php $isLow = $p->stock_qty <= $p->stock_minimum; @endphp
                            <tr>
                                <td><code style="font-size: 0.78rem;">{{ $p->sku }}</code></td>
                                <td>
                                    <strong style="display: block; font-size: 0.875rem;">{{ $p->name }}</strong>
                                    {{-- visible on mobile only --}}
                                    <span class="show-mobile-only" style="display: none;">
                                        <span class="badge badge-info" style="font-size: 0.65rem; margin-top: 4px;">{{ $p->category->name ?? 'Lainnya' }}</span>
                                        @if($p->is_active)
                                            <span class="badge badge-success" style="font-size: 0.65rem; margin-top: 4px;">Aktif</span>
                                        @else
                                            <span class="badge badge-danger" style="font-size: 0.65rem; margin-top: 4px;">Non-Aktif</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="hide-mobile">
                                    <span class="badge badge-info">{{ $p->category->name ?? 'Lainnya' }}</span>
                                </td>
                                <td class="text-right hide-mobile" style="color: var(--text-muted); font-size: 0.85rem;">
                                    Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="text-right" style="font-weight: 700; color: var(--cyan); font-size: 0.875rem;">
                                    Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    <span style="font-weight: 700; font-size: 0.875rem; color: {{ $isLow ? 'var(--error)' : 'var(--text-main)' }};">
                                        {{ $p->stock_qty }}
                                    </span>
                                    <small style="color: var(--text-muted);">{{ $p->unit }}</small>
                                    @if($isLow)
                                        <i data-lucide="alert-circle" style="width: 12px; height: 12px; color: var(--error); vertical-align: middle;" title="Stok menipis!"></i>
                                    @endif
                                </td>
                                <td class="hide-mobile">
                                    @if($p->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="{{ route('products.show', $p->id) }}" class="btn btn-secondary btn-sm" title="Detail">
                                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $p->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                                            <i data-lucide="edit-3" style="width: 13px; height: 13px;"></i>
                                        </a>
                                        @if($p->is_active)
                                            <form action="{{ route('products.destroy', $p->id) }}" method="POST"
                                                onsubmit="return confirm('Nonaktifkan produk ini?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Deaktifkan">
                                                    <i data-lucide="trash" style="width: 13px; height: 13px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; flex-wrap: wrap; gap: 12px;">
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                    dari {{ $products->total() }} produk
                </span>
                <div>{{ $products->links() }}</div>
            </div>
        @endif
    </div>

@endsection

@section('styles')
<style>
    @media (max-width: 768px) {
        .hide-mobile { display: none !important; }
        .show-mobile-only { display: inline-flex !important; gap: 4px; flex-wrap: wrap; }
    }

    @media (max-width: 640px) {
        .pagination-wrapper { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection