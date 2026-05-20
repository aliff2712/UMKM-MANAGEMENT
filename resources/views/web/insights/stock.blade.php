@extends('layouts.app')

@section('title', 'Insight Inventaris')
@section('page_title', 'Insight Stok & Inventaris')

@section('content')
    <!-- Stat Grid -->
    <div class="stat-grid">
        <div class="glass-card stat-card error">
            <div class="stat-card-label">Kehabisan Stok (Kritis)</div>
            <div class="stat-card-value text-error">{{ $criticalProducts->count() }}</div>
            <div class="stat-card-desc">Barang yang bernilai 0 dan tidak bisa ditransaksikan</div>
        </div>

        <div class="glass-card stat-card warning">
            <div class="stat-card-label">Stok Menipis (Warning)</div>
            <div class="stat-card-value text-warning">{{ $warningProducts->count() }}</div>
            <div class="stat-card-desc">Barang dengan sisa stok di bawah batas minimal</div>
        </div>

        <div class="glass-card stat-card success">
            <div class="stat-card-label">Total Alert</div>
            <div class="stat-card-value text-success">{{ $totalLowStock }}</div>
            <div class="stat-card-desc">Total barang yang butuh pengisian ulang</div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="glass-card mb-6 flex-between">
        <span>Gunakan menu penyesuaian stok untuk menambah persediaan barang secara manual.</span>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('stock.adjust') }}" class="btn btn-primary">
                <i data-lucide="plus-circle"></i>
                <span>Sesuaikan Stok</span>
            </a>
            <a href="{{ route('insights.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <!-- Critical Section -->
    <div class="glass-card mb-6">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-error">
                <i data-lucide="x-circle"></i>
                <span>Produk Kehabisan Stok (Stok = 0)</span>
            </h3>
            <span class="badge badge-danger">Harus Segera Diisi</span>
        </div>

        @if($criticalProducts->isEmpty())
            <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                <i data-lucide="shield-check" style="width: 36px; height: 36px; margin-bottom: 8px; color: var(--success);"></i>
                <p>Hebat! Tidak ada produk yang kehabisan stok saat ini.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th class="text-right">Minimal Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criticalProducts as $p)
                            <tr>
                                <td><strong>{{ $p['name'] }}</strong></td>
                                <td><code>{{ $p['sku'] }}</code></td>
                                <td class="text-right">{{ $p['min_stock'] }} {{ $p['unit'] }}</td>
                                <td>
                                    <a href="{{ route('products.show', $p['id']) }}" class="btn btn-secondary btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Warning Section -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="alert-triangle"></i>
                <span>Stok Di Bawah Batas Minimum</span>
            </h3>
            <span class="badge badge-warning">Peringatan</span>
        </div>

        @if($warningProducts->isEmpty())
            <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                <i data-lucide="check-circle" style="width: 36px; height: 36px; margin-bottom: 8px; color: var(--success);"></i>
                <p>Semua produk masih di atas batas aman minimum.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th class="text-right">Stok Sekarang</th>
                            <th class="text-right">Minimal Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warningProducts as $p)
                            <tr>
                                <td><strong>{{ $p['name'] }}</strong></td>
                                <td><code>{{ $p['sku'] }}</code></td>
                                <td class="text-right text-warning"><strong>{{ $p['stock_qty'] }}</strong> <small>{{ $p['unit'] }}</small></td>
                                <td class="text-right">{{ $p['min_stock'] }} {{ $p['unit'] }}</td>
                                <td>
                                    <a href="{{ route('products.show', $p['id']) }}" class="btn btn-secondary btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
