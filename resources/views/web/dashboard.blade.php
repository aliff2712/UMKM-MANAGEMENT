@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Bisnis')

@section('content')
    <!-- Stat Grid -->
    <div class="stat-grid">
        <!-- Pendapatan Card -->
        <div class="glass-card stat-card cyan">
            <div class="stat-card-label">Total Pendapatan</div>
            <div class="stat-card-value text-cyan">Rp {{ number_format($financialInsight['total_revenue'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">
                <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                <span>Periode: {{ $period }}</span>
            </div>
        </div>

        <!-- Pengeluaran Card -->
        <div class="glass-card stat-card error">
            <div class="stat-card-label">Total Pengeluaran</div>
            <div class="stat-card-value text-error">Rp {{ number_format($financialInsight['total_expenses'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">
                <i data-lucide="wallet" style="width: 14px; height: 14px;"></i>
                <span>Biaya operasional outlet</span>
            </div>
        </div>

        <!-- Laba Bersih Card -->
        @php
            $isProfit = ($financialInsight['net_profit'] ?? 0) >= 0;
        @endphp
        <div class="glass-card stat-card {{ $isProfit ? 'success' : 'error' }}">
            <div class="stat-card-label">Laba Bersih</div>
            <div class="stat-card-value {{ $isProfit ? 'text-success' : 'text-error' }}">
                Rp {{ number_format($financialInsight['net_profit'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="stat-card-desc">
                <i data-lucide="{{ $isProfit ? 'smile' : 'frown' }}" style="width: 14px; height: 14px;"></i>
                <span>Status: {{ $financialInsight['status'] ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Margin Profitabilitas Card -->
        <div class="glass-card stat-card primary">
            <div class="stat-card-label">Margin Profit</div>
            <div class="stat-card-value text-primary">{{ number_format($financialInsight['profit_margin'] ?? 0, 1, ',', '.') }}%</div>
            <div class="stat-card-desc">
                <i data-lucide="percent" style="width: 14px; height: 14px;"></i>
                <span>Rasio profitabilitas bersih</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid-2">
        
        <!-- Left Side: Stock & Low Alert -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-warning">
                    <i data-lucide="alert-triangle"></i>
                    <span>Stok Rendah & Kritis</span>
                </h3>
                <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm">Kelola Stok</a>
            </div>

            @if($lowStockProducts->isEmpty())
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="check-circle" style="width: 48px; height: 48px; margin-bottom: 12px; color: var(--success);"></i>
                    <p>Semua stok produk aman dan di atas batas minimum.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th class="text-right">Sisa Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr>
                                    <td><strong>{{ $product->name }}</strong></td>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td class="text-right"><strong>{{ $product->stock_qty }}</strong> <small>{{ $product->unit }}</small></td>
                                    <td>
                                        @if($product->stock_qty == 0)
                                            <span class="badge badge-danger">Habis</span>
                                        @else
                                            <span class="badge badge-warning">Kritis</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Right Side: Top Products & Trends -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-success">
                    <i data-lucide="award"></i>
                    <span>Produk Terlaris</span>
                </h3>
                <span class="badge badge-success">Top 5</span>
            </div>

            @if($topProducts->isEmpty())
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="shopping-bag" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
                    <p>Belum ada data penjualan tercatat untuk outlet ini.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Terjual</th>
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

    </div>

    <!-- Second Grid Row: Declining Products (Tabel Khusus Owner) -->
    @if(auth()->user()->role === 'owner' && !$decliningProducts->isEmpty())
        <div class="glass-card mt-4">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-error">
                    <i data-lucide="trending-down"></i>
                    <span>Produk Mengalami Penurunan Tren Penjualan</span>
                </h3>
                <span class="badge badge-danger">Perlu Tindakan</span>
            </div>
            
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-right">Penjualan Bulan Lalu</th>
                            <th class="text-right">Penjualan Bulan Ini</th>
                            <th class="text-right">Persentase Penurunan</th>
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
        </div>
    @endif
@endsection
