@extends('layouts.app')

@section('title', 'Insight Penjualan')
@section('page_title', 'Insight Penjualan & Produk')

@section('content')
    <!-- Filter Period Form -->
    <div class="glass-card mb-6">
        <form action="{{ route('insights.sales') }}" method="GET" class="flex-between grid-2" style="align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="period">Pilih Periode Analisis</label>
                <input type="month" name="period" id="period" class="form-control" value="{{ $period }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="filter"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('insights.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    <!-- Product Analytics Sections -->
    <div class="grid-2">
        <!-- Top Selling -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-success">
                    <i data-lucide="trending-up"></i>
                    <span>Produk Terlaris & Kontribusi Omzet</span>
                </h3>
            </div>

            @if(empty($topProducts) || count($topProducts) == 0)
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="package-search" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
                    <p>Tidak ada penjualan produk pada periode ini.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Qty Terjual</th>
                                <th class="text-right">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $p)
                                <tr>
                                    <td><strong>{{ $p->name }}</strong></td>
                                    <td class="text-right"><strong>{{ $p->total_qty }}</strong> <small>{{ $p->unit }}</small></td>
                                    <td class="text-right text-cyan">Rp {{ number_format($p->total_sales, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Declining Products -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-error">
                    <i data-lucide="trending-down"></i>
                    <span>Penurunan Tren Penjualan</span>
                </h3>
            </div>

            @if(empty($decliningProducts) || count($decliningProducts) == 0)
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 48px; height: 48px; margin-bottom: 12px; color: var(--success);"></i>
                    <p>Semua produk mempertahankan tren penjualan dengan baik!</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Bulan Lalu</th>
                                <th class="text-right">Bulan Ini</th>
                                <th class="text-right">Penurunan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($decliningProducts as $dp)
                                <tr>
                                    <td><strong>{{ $dp['name'] }}</strong></td>
                                    <td class="text-right">{{ $dp['prev_sales'] }}</td>
                                    <td class="text-right"><strong>{{ $dp['current_sales'] }}</strong></td>
                                    <td class="text-right text-error font-bold">-{{ number_format($dp['drop_percentage'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
