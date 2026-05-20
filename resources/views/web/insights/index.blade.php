@extends('layouts.app')

@section('title', 'Business Insights')
@section('page_title', 'Business Insights')

@section('content')
    <!-- Insight Sections Links -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <div class="glass-card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px; gap: 16px;">
            <div class="brand-icon" style="background: rgba(6, 182, 212, 0.2); color: var(--cyan); width: 64px; height: 64px; border-radius: 50%;">
                <i data-lucide="bar-chart-3" style="width: 32px; height: 32px;"></i>
            </div>
            <h3>Insight Penjualan</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center;">Analisis produk paling laku, kontribusi omzet, dan barang dengan tren penjualan menurun.</p>
            <a href="{{ route('insights.sales') }}" class="btn btn-primary btn-sm mt-4">Lihat Analisis Penjualan</a>
        </div>

        <div class="glass-card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px; gap: 16px;">
            <div class="brand-icon" style="background: rgba(245, 158, 11, 0.2); color: var(--warning); width: 64px; height: 64px; border-radius: 50%;">
                <i data-lucide="boxes" style="width: 32px; height: 32px;"></i>
            </div>
            <h3>Insight Inventaris</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center;">Deteksi dini produk kritis, barang kehabisan stok, serta peringatan batas aman inventaris.</p>
            <a href="{{ route('insights.stock') }}" class="btn btn-accent btn-sm mt-4">Lihat Analisis Stok</a>
        </div>

        <div class="glass-card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px; gap: 16px;">
            <div class="brand-icon" style="background: rgba(16, 185, 129, 0.2); color: var(--success); width: 64px; height: 64px; border-radius: 50%;">
                <i data-lucide="dollar-sign" style="width: 32px; height: 32px;"></i>
            </div>
            <h3>Insight Finansial</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center;">Pantau laba bersih bulanan, margin keuntungan outlet, serta rasio kesehatan arus kas.</p>
            <a href="{{ route('insights.financial') }}" class="btn btn-secondary btn-sm mt-4">Lihat Analisis Keuangan</a>
        </div>
    </div>

    <!-- Quick Insights Overview -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-cyan">
                <i data-lucide="eye"></i>
                <span>Ringkasan Performa Outlet (Bulan Ini)</span>
            </h3>
            <span class="badge badge-info">{{ $period }}</span>
        </div>
        
        <div class="grid-2">
            <div>
                <h4 class="mb-4 text-primary">Kesehatan Finansial</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Total Pendapatan:</span>
                        <strong class="text-cyan">Rp {{ number_format($financialInsight['total_revenue'] ?? 0, 0, ',', '.') }}</strong>
                    </li>
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Total Biaya Operasional:</span>
                        <strong class="text-error">Rp {{ number_format($financialInsight['total_expenses'] ?? 0, 0, ',', '.') }}</strong>
                    </li>
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Status Laba Bersih:</span>
                        <strong class="{{ ($financialInsight['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                            Rp {{ number_format($financialInsight['net_profit'] ?? 0, 0, ',', '.') }} ({{ $financialInsight['status'] ?? 'N/A' }})
                        </strong>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 text-warning">Kondisi Inventaris</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Produk Kehabisan Stok:</span>
                        <strong class="text-error">{{ count($stockInsight['critical'] ?? []) }} Barang</strong>
                    </li>
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Produk Di Bawah Stok Minimum:</span>
                        <strong class="text-warning">{{ count($stockInsight['warning'] ?? []) }} Barang</strong>
                    </li>
                    <li class="flex-between" style="padding: 10px; background: rgba(255,255,255,0.02); border-radius: var(--border-radius-sm);">
                        <span>Tingkat Ketersediaan Barang:</span>
                        <span class="badge {{ $stockInsight['total_low_stock'] > 3 ? 'badge-danger' : 'badge-success' }}">
                            {{ $stockInsight['total_low_stock'] > 3 ? 'Kritis' : 'Aman' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
