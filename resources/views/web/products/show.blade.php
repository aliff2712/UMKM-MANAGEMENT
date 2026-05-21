@extends('layouts.app')

@section('title', 'Detail Produk')
@section('page_title', 'Detail Produk')

@section('content')
    <!-- Detail Cards and Stock History -->
    <div class="grid-2 mb-6">
        <!-- Specs Info -->
        <div class="glass-card">
            <div class="glass-card-header">
<div class="mb-4">
    @if($product->image_path)
        <img src="{{ $product->image_url }}" alt="Produk Image" class="img-thumbnail" style="max-width:300px;">
    @else
        <p class="text-muted">Tidak ada gambar produk.</p>
    @endif
</div>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm">
                        <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                        <span>Edit</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>

            <ul style="list-style: none; display: flex; flex-direction: column; gap: 14px;" class="mt-4">
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Kode SKU:</span>
                    <code>{{ $product->sku }}</code>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Nama Produk:</span>
                    <strong>{{ $product->name }}</strong>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Kategori:</span>
                    <span class="badge badge-info">{{ $product->category->name ?? 'Lainnya' }}</span>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Harga Beli Bruto:</span>
                    <strong>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</strong>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Harga Jual Net:</span>
                    <strong class="text-cyan">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</strong>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Sisa Stok Sekarang:</span>
                    @php
                        $isLow = $product->stock_qty <= $product->stock_minimum;
                    @endphp
                    <span class="{{ $isLow ? 'text-error font-bold' : 'text-success' }}">
                        <strong>{{ $product->stock_qty }}</strong> {{ $product->unit }}
                        @if($isLow)
                            <small class="badge badge-danger" style="font-size: 0.6rem; padding: 2px 6px;">Stok Rendah</small>
                        @endif
                    </span>
                </li>
                <li class="flex-between" style="padding-bottom: 10px; border-bottom: 1px solid var(--glass-border);">
                    <span style="color: var(--text-muted)">Batas Stok Minimal:</span>
                    <span>{{ $product->stock_minimum }} {{ $product->unit }}</span>
                </li>
                <li class="flex-between" style="padding-bottom: 10px;">
                    <span style="color: var(--text-muted)">Status Registrasi:</span>
                    @if($product->is_active)
                        <span class="badge badge-success">Aktif / Dijual</span>
                    @else
                        <span class="badge badge-danger">Non-Aktif</span>
                    @endif
                </li>
            </ul>
        </div>

        <!-- Mini Insights or Alert -->
        <div class="glass-card flex-between" style="flex-direction: column; align-items: stretch; gap: 20px;">
            <div>
                <h4 class="text-primary mb-4">Analisis Cepat Produk</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5;" class="mb-4">
                    Produk ini terdaftar untuk outlet <strong>{{ $product->outlet->name ?? 'Outlet Utama' }}</strong>. Harga jual barang diset dengan markup margin keuntungan sebesar 
                    @php
                        $profitPercent = $product->purchase_price > 0 ? (($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100 : 0;
                    @endphp
                    <strong class="text-success">{{ number_format($profitPercent, 1) }}%</strong> dari harga beli bruto.
                </p>
            </div>
            
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 16px; border-radius: var(--border-radius-md);">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <i data-lucide="help-circle" class="text-cyan" style="flex-shrink: 0; margin-top: 2px;"></i>
                    <p style="font-size: 0.8rem; line-height: 1.4; color: var(--text-muted);">
                        Butuh pengisian stok produk ini? Anda bisa melakukan *restocking* secara cepat di halaman manajemen stok manual dengan sekali klik.
                    </p>
                </div>
                <a href="{{ route('stock.adjust') }}?product_id={{ $product->id }}" class="btn btn-secondary btn-sm mt-4" style="width: 100%;">Sesuaikan Stok Produk</a>
            </div>
        </div>
    </div>

    <!-- Stock Movements Table (Latest 20) -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="history"></i>
                <span>Log Pergerakan Stok (20 Terakhir)</span>
            </h3>
        </div>

        @if($stockMovements->isEmpty())
            <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                <i data-lucide="arrow-left-right" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
                <p>Belum ada riwayat pergerakan stok tercatat untuk produk ini.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Waktu & Tanggal</th>
                            <th>Petugas</th>
                            <th>Jenis Perubahan</th>
                            <th class="text-right">Jumlah (Quantity)</th>
                            <th>Keterangan / Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockMovements as $move)
                            <tr>
                                <td>{{ $move->created_at->format('d M Y - H:i') }}</td>
                                <td><strong>{{ $move->user->name ?? 'System' }}</strong></td>
                                <td>
                                    @if($move->type === 'in')
                                        <span class="badge badge-success">Stok Masuk</span>
                                    @elseif($move->type === 'out')
                                        <span class="badge badge-info">Stok Keluar</span>
                                    @else
                                        <span class="badge badge-warning">Penyesuaian</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold {{ $move->type === 'in' ? 'text-success' : ($move->type === 'out' ? 'text-cyan' : 'text-warning') }}">
                                    {{ $move->type === 'in' ? '+' : ($move->type === 'out' ? '-' : '') }}{{ $move->qty }}
                                </td>
                                <td><small>{{ $move->note ?? '-' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
