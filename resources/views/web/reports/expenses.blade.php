@extends('layouts.app')

@section('title', 'Laporan Pengeluaran')
@section('page_title', 'Laporan Kinerja Pengeluaran')

@section('content')
<!-- Filter Date Period Form -->
{{-- Form filter — ganti yang lama --}}
<div class="glass-card mb-6">
    <form action="{{ route('reports.expenses') }}" method="GET" class="report-filter-form" id="filter-form">
        <div class="report-filter-inputs">
            <div class="form-group">
                <label class="form-label" for="start_date">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="end_date">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
        </div>
        <div class="report-filter-actions">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="filter"></i>
                <span>Terapkan Filter</span>
            </button>
            <button type="button" class="btn btn-success" onclick="exportReport('csv')">
                <i data-lucide="download"></i>
                <span>Ekspor CSV</span>
            </button>
            <button type="button" class="btn btn-info" onclick="exportReport('excel')">
                <i data-lucide="file-text"></i>
                <span>Ekspor Excel</span>
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>

    <form id="export-form" action="{{ route('reports.expenses.export') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
        <input type="hidden" name="format" id="export-format" value="csv">
    </form>
</div>

<!-- Summary Performance KPI Grid -->
<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    <div class="glass-card stat-card error" style="flex: 1;">
        <div class="stat-card-label">Total Nominal Pengeluaran</div>
        <div class="stat-card-value text-error">Rp {{ number_format($summary['total_amount'] ?? 0, 0, ',', '.') }}</div>
        <div class="stat-card-desc">Total belanja operasional outlet</div>
    </div>

    <div class="glass-card stat-card warning" style="flex: 1;">
        <div class="stat-card-label">Jumlah Pencatatan (Kuitansi)</div>
        <div class="stat-card-value text-warning">{{ number_format($summary['total_records'] ?? 0, 0, ',', '.') }}</div>
        <div class="stat-card-desc">Total nota pengeluaran terekam</div>
    </div>
</div>

<!-- Expense distribution report -->
<div class="glass-card">
    <div class="glass-card-header">
        <h3 class="glass-card-title text-error">
            <i data-lucide="pie-chart"></i>
            <span>Distribusi Pengeluaran berdasarkan Kategori</span>
        </h3>
        <span class="badge badge-danger">Alokasi Anggaran</span>
    </div>

    @if(empty($byCategory) || count($byCategory) == 0)
    <div style="text-align: center; padding: 48px; color: var(--text-muted);">
        <i data-lucide="receipt-x" style="width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.5;"></i>
        <p>Tidak ada catatan pengeluaran terekam dalam periode filter saat ini.</p>
    </div>
    @else
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Kategori Pengeluaran</th>
                    <th class="text-right">Frekuensi Nota</th>
                    <th class="text-right">Total Anggaran Dialokasikan</th>
                </tr>
            </thead>
            <tbody>
                @php
                $grandTotalExpenses = $summary['total_amount'] ?: 1;
                @endphp
                {{-- Foreach $byCategory — ganti yang lama --}}
                @foreach($byCategory as $data)
                @php
                $catContribution = ($data['total'] / $grandTotalExpenses) * 100;
                @endphp
                <tr>
                    <td><strong>{{ $data['category_name'] }}</strong></td>
                    <td class="text-right"><strong>{{ $data['count'] }}</strong> kali</td>
                    <td class="text-right">
                        <div style="text-align: right; margin-bottom: 4px;">
                            <strong class="text-error">Rp {{ number_format($data['total'], 0, ',', '.') }}</strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;">
                                ({{ number_format($catContribution, 1) }}%)
                            </span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden;">
                            <div style="width: {{ $catContribution }}%; height: 100%; background: var(--error); border-radius: 99px;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<script>
function exportReport(format) {
    const form = document.getElementById('export-form');
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    form.querySelector('input[name="start_date"]').value = startDate;
    form.querySelector('input[name="end_date"]').value = endDate;
    form.querySelector('#export-format').value = format;
    
    form.submit();
}
</script>
@endsection