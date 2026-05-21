@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Bisnis')

@section('content')

<!-- ========================= -->
<!-- STAT GRID                 -->
<!-- ========================= -->
<div class="stat-grid">

    <div class="glass-card stat-card cyan">
        <div class="stat-card-label">Total Pendapatan</div>
        <div class="stat-card-value text-cyan">
            Rp {{ number_format($financialInsight['total_revenue'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="stat-card-desc">
            <i data-lucide="trending-up" style="width:14px;height:14px;"></i>
            <span>Periode: {{ $period }}</span>
        </div>
    </div>

    <div class="glass-card stat-card error">
        <div class="stat-card-label">Total Pengeluaran</div>
        <div class="stat-card-value text-error">
            Rp {{ number_format($financialInsight['total_expenses'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="stat-card-desc">
            <i data-lucide="wallet" style="width:14px;height:14px;"></i>
            <span>Biaya operasional outlet</span>
        </div>
    </div>

    @php $isProfit = ($financialInsight['net_profit'] ?? 0) >= 0; @endphp
    <div class="glass-card stat-card {{ $isProfit ? 'success' : 'error' }}">
        <div class="stat-card-label">Laba Bersih</div>
        <div class="stat-card-value {{ $isProfit ? 'text-success' : 'text-error' }}">
            Rp {{ number_format($financialInsight['net_profit'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="stat-card-desc">
            <i data-lucide="{{ $isProfit ? 'smile' : 'frown' }}" style="width:14px;height:14px;"></i>
            <span>Status: {{ $financialInsight['status'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="glass-card stat-card primary">
        <div class="stat-card-label">Margin Profit</div>
        <div class="stat-card-value text-primary">
            {{ number_format($financialInsight['profit_margin'] ?? 0, 1, ',', '.') }}%
        </div>
        <div class="stat-card-desc">
            <i data-lucide="percent" style="width:14px;height:14px;"></i>
            <span>Rasio profitabilitas bersih</span>
        </div>
    </div>

</div>

<!-- ========================= -->
<!-- CHARTS ROW                -->
<!-- ========================= -->
<div class="dashboard-grid-2col">

    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-primary">
                <i data-lucide="line-chart"></i>
                <span>Produk Terlaris (Qty Terjual)</span>
            </h3>
        </div>
        <div class="chart-wrapper">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="alert-triangle"></i>
                <span>Stok Menipis (Qty Tersisa)</span>
            </h3>
        </div>
        <div class="chart-wrapper">
            <canvas id="paymentChart"></canvas>
        </div>
    </div>

</div>

<!-- ========================= -->
<!-- TABLES ROW                -->
<!-- ========================= -->
<div class="dashboard-grid-2col">

    <!-- Barang Menipis -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="alert-triangle"></i>
                <span>Barang Menipis</span>
            </h3>
            <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm">
                Kelola Stok
            </a>
        </div>

        @if($lowStockProducts->isEmpty())
            <div class="empty-state">
                <i data-lucide="check-circle" style="width:40px;height:40px;color:var(--success);margin-bottom:10px;"></i>
                <p>Semua stok produk aman.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-right">Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                                    <code>{{ $product->sku }}</code>
                                </div>
                            </td>
                            <td class="text-right">
                                <strong>{{ $product->stock_qty }}</strong>
                                <small>{{ $product->unit }}</small>
                            </td>
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

    <!-- Produk Terlaris -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-success">
                <i data-lucide="award"></i>
                <span>Produk Terlaris</span>
            </h3>
            <span class="badge badge-success">Top 5</span>
        </div>

        @if($topProducts->isEmpty())
            <div class="empty-state">
                <i data-lucide="shopping-bag" style="width:40px;height:40px;margin-bottom:10px;color:var(--text-muted);"></i>
                <p>Belum ada data penjualan.</p>
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
                            <td class="text-right">
                                <strong>{{ $p->total_sold }}</strong>
                                <small>{{ $p->unit }}</small>
                            </td>
                            <td class="text-right text-cyan">
                                Rp {{ number_format($p->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<!-- ========================= -->
<!-- DECLINING PRODUCTS        -->
<!-- ========================= -->
@if(auth()->user()->role === 'owner' && !$decliningProducts->isEmpty())
<div class="glass-card mt-4">
    <div class="glass-card-header">
        <h3 class="glass-card-title text-error">
            <i data-lucide="trending-down"></i>
            <span>Penurunan Tren Penjualan</span>
        </h3>
        <span class="badge badge-danger">Perlu Tindakan</span>
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Bln Lalu</th>
                    <th class="text-right">Bln Ini</th>
                    <th class="text-right">Turun</th>
                </tr>
            </thead>
            <tbody>
                @foreach($decliningProducts as $dp)
                <tr>
                    <td><strong>{{ $dp['name'] }}</strong></td>
                    <td class="text-right">{{ $dp['last_qty'] }}</td>
                    <td class="text-right"><strong>{{ $dp['current_qty'] }}</strong></td>
                    <td class="text-right text-error" style="font-weight:700;">
                        -{{ number_format($dp['decline_percent  '], 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    const topProductsLabels = [
        @foreach($topProducts as $p) "{{ $p->name }}", @endforeach
    ];
    const topProductsSales = [
        @foreach($topProducts as $p) {{ $p->total_sold }}, @endforeach
    ];
    const lowStockLabels = [
        @foreach($lowStockProducts as $product) "{{ $product->name }}", @endforeach
    ];
    const lowStockData = [
        @foreach($lowStockProducts as $product) {{ $product->stock_qty }}, @endforeach
    ];

    // Sales Trend Chart → sekarang: Produk Terlaris by Qty
    new Chart(document.getElementById('salesTrendChart'), {
        type: 'bar',
        data: {
            labels: topProductsLabels,
            datasets: [{
                label: 'Qty Terjual',
                data: topProductsSales,
                backgroundColor: ['#06b6d4','#8b5cf6','#10b981','#f59e0b','#ef4444'],
                borderRadius: 10,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#64748b', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} pcs terjual`
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10 },
                        maxRotation: 30,
                        callback: function(val, i) {
                            // Potong label panjang biar gak overflow
                            const label = this.getLabelForValue(val);
                            return label.length > 12 ? label.slice(0, 12) + '…' : label;
                        }
                    },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 1 },
                    grid: { color: 'rgba(148,163,184,0.1)' }
                }
            }
        }
    });

    // Payment Chart → sekarang: Stok Menipis by Qty
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: lowStockLabels.length ? lowStockLabels : ['Semua Stok Aman'],
            datasets: [{
                data: lowStockData.length ? lowStockData : [1],
                backgroundColor: lowStockData.length
                    ? ['#ef4444','#f59e0b','#8b5cf6','#06b6d4','#10b981']
                    : ['#e2e8f0'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#64748b', padding: 14, font: { size: 11 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` Sisa stok: ${ctx.parsed} pcs`
                    }
                }
            }
        }
    });

</script>
@endpush