@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page_title', 'Laporan Kinerja Penjualan')

@section('content')
    <div class="glass-card mb-6">
        <form action="{{ route('reports.sales') }}" method="GET" class="flex-between grid-2" style="align-items: flex-end;">
            <div style="display: flex; gap: 16px; flex: 1;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label" for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? date('Y-m-d') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label" for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? date('Y-m-d') }}">
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="filter"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    <div class="stat-grid">
        <div class="glass-card stat-card cyan">
            <div class="stat-card-label">Total Omzet Penjualan</div>
            <div class="stat-card-value text-cyan">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Total omzet kotor penjualan</div>
        </div>

        <div class="glass-card stat-card success">
            <div class="stat-card-label">Jumlah Barang Terjual</div>
            <div class="stat-card-value text-success">{{ number_format($summary['total_qty'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Total item produk yang keluar</div>
        </div>

        <div class="glass-card stat-card primary">
            <div class="stat-card-label">Volume Transaksi</div>
            <div class="stat-card-value text-primary">{{ number_format($summary['transaction_count'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Total kuitansi/struk belanja tercetak</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-primary"><i data-lucide="box"></i> <span>Kinerja Kontribusi Produk</span></h3>
            </div>

            @if(empty($productSales) || count($productSales) == 0)
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <p>Tidak ada kontribusi produk tercatat pada periode ini.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Kontribusi Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotalSales = ($summary['total_sales'] ?? 1) ?: 1; @endphp
                            @foreach($productSales as $ps)
                                @php 
                                    // Mengambil nilai secara aman
                                    $sales = $ps->total_sales ?? 0;
                                    $qty = $ps->total_qty ?? 0;
                                    $itemContribution = ($sales / $grandTotalSales) * 100;
                                @endphp
                                <tr>
                                    <td><strong>{{ $ps->name ?? 'Produk Tidak Dikenal' }}</strong></td>
                                    <td class="text-right"><strong>{{ $qty }}</strong></td>
                                    <td class="text-right">
                                        <div style="text-align: right; margin-bottom: 4px;">
                                            <strong class="text-cyan">Rp {{ number_format($sales, 0, ',', '.') }}</strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;">({{ number_format($itemContribution, 1) }}%)</span>
                                        </div>
                                        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.06); border-radius: 99px;">
                                            <div style="width: {{ $itemContribution }}%; height: 100%; background: var(--cyan); border-radius: 99px;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="glass-card">
            <div class="glass-card-header">
                <h3 class="glass-card-title text-success"><i data-lucide="credit-card"></i> <span>Analisis Metode Pembayaran</span></h3>
            </div>

            @if(empty($byPayment) || count($byPayment) == 0)
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <p>Belum ada data pembayaran terdeteksi.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Metode Bayar</th>
                                <th class="text-right">Struk</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byPayment as $method => $data)
                                <tr>
                                    <td><span class="badge">{{ strtoupper($method) }}</span></td>
                                    <td class="text-right"><strong>{{ $data['count'] ?? 0 }}</strong></td>
                                    <td class="text-right text-success font-bold">
                                        Rp {{ number_format($data['amount'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection