@extends('layouts.app')

@section('title', 'Kelola Produk')
@section('page_title', 'Daftar Produk Outlet')

@section('content')
    <!-- Actions and Filters Bar -->
    <div class="glass-card mb-6">
        <form action="{{ route('products.index') }}" method="GET" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="flex-between">
                <h4 style="font-weight: 700;">Filter Pencarian</h4>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i data-lucide="plus-circle"></i>
                    <span>Tambah Produk Baru</span>
                </a>
            </div>
            
            <div class="form-grid" style="align-items: flex-end;">
                <!-- Search text -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search">Cari Nama / SKU</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Ketik nama atau SKU..." value="{{ $filters['search'] ?? '' }}">
                </div>
                
                <!-- Category select -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="category_id">Kategori</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Low Stock checkbox -->
                <div class="form-group" style="margin-bottom: 0; padding-bottom: 12px;">
                    <label class="form-check">
                        <input type="checkbox" name="low_stock" value="1" {{ ($filters['low_stock'] ?? '') ? 'checked' : '' }}>
                        <span>Stok Rendah Saja</span>
                    </label>
                </div>

                <!-- Action Button -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-secondary" style="flex: 1;">
                        <i data-lucide="search"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary" title="Reset Filter">
                        <i data-lucide="refresh-cw"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Products Table Card -->
    <div class="glass-card">
        @if($products->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
                <i data-lucide="package-x" style="width: 64px; height: 64px; margin-bottom: 16px;"></i>
                <p>Tidak ada produk ditemukan. Silakan tambahkan produk baru atau sesuaikan filter Anda.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-right">Harga Beli</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Sisa Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                            @php
                                $isLow = $p->stock_qty <= $p->stock_minimum;
                            @endphp
                            <tr>
                                <td><code>{{ $p->sku }}</code></td>
                                <td><strong>{{ $p->name }}</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ $p->category->name ?? 'Lainnya' }}</span>
                                </td>
                                <td class="text-right"><small>Rp</small> {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                                <td class="text-right text-cyan"><small>Rp</small> {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                                <td class="text-right {{ $isLow ? 'text-error font-bold' : '' }}">
                                    {{ $p->stock_qty }} <small>{{ $p->unit }}</small>
                                    @if($isLow)
                                        <i data-lucide="alert-circle" style="width: 12px; height: 12px; vertical-align: middle;" class="text-error" title="Stok menipis!"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($p->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="{{ route('products.show', $p->id) }}" class="btn btn-secondary btn-sm" title="Detail & Riwayat Stok">
                                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $p->id) }}" class="btn btn-secondary btn-sm" title="Edit Produk">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        
                                        @if($p->is_active)
                                            <form action="{{ route('products.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan produk ini?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Deaktifkan">
                                                    <i data-lucide="trash" style="width: 14px; height: 14px;"></i>
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

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
