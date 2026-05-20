@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('page_title', 'Kelola Stok & Persediaan')

@section('content')
    <!-- Stat Grid -->
    <div class="stat-grid">
        <div class="glass-card stat-card primary">
            <div class="stat-card-label">Total Jenis Produk</div>
            <div class="stat-card-value text-primary">{{ $totalProducts }}</div>
            <div class="stat-card-desc">Produk terdaftar di outlet Anda</div>
        </div>

        <div class="glass-card stat-card warning">
            <div class="stat-card-label">Stok Menipis (Warning)</div>
            <div class="stat-card-value text-warning">{{ $totalLowStock }}</div>
            <div class="stat-card-desc">Barang di bawah batas minimum aman</div>
        </div>

        <div class="glass-card stat-card error">
            <div class="stat-card-label">Habis (Out of Stock)</div>
            <div class="stat-card-value text-error">{{ $totalOutOfStock }}</div>
            <div class="stat-card-desc">Barang dengan stok bernilai 0</div>
        </div>
    </div>

    <!-- Actions and Status -->
    <div class="glass-card mb-6 flex-between">
        <span>Catat stok masuk pembelian baru atau perbaiki pencatatan salah melalui form koreksi stok manual.</span>
        <a href="{{ route('stock.adjust') }}" class="btn btn-primary">
            <i data-lucide="plus-circle"></i>
            <span>Penyesuaian Stok Manual</span>
        </a>
    </div>

    <!-- Low Stock Alert List Card -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="alert-triangle"></i>
                <span>Daftar Produk Perlu Pengisian Stok</span>
            </h3>
            <span class="badge badge-danger">Perhatian</span>
        </div>

        @if($lowStockProducts->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
                <i data-lucide="shield-check" style="width: 64px; height: 64px; margin-bottom: 16px; color: var(--success);"></i>
                <p>Hebat! Persediaan produk Anda sangat baik, tidak ada barang dengan stok kritis saat ini.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th class="text-right">Sisa Stok Sekarang</th>
                            <th class="text-right">Batas Stok Minimum</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $p)
                            <tr>
                                <td><code>{{ $p->sku }}</code></td>
                                <td><strong>{{ $p->name }}</strong></td>
                                <td class="text-right text-error font-bold">{{ $p->stock_qty }} <small>{{ $p->unit }}</small></td>
                                <td class="text-right">{{ $p->stock_minimum }} {{ $p->unit }}</td>
                                <td>
                                    @if($p->stock_qty == 0)
                                        <span class="badge badge-danger">Habis</span>
                                    @else
                                        <span class="badge badge-warning">Kritis</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('stock.adjust') }}?product_id={{ $p->id }}" class="btn btn-primary btn-sm">
                                            <i data-lucide="plus"></i>
                                            <span>Tambah Stok</span>
                                        </a>
                                        <a href="{{ route('products.show', $p->id) }}" class="btn btn-secondary btn-sm">Riwayat</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
