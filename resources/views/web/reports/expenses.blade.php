@extends('layouts.app')

@section('title', 'Laporan Pengeluaran')
@section('page_title', 'Laporan Kinerja Pengeluaran')

@section('content')
    <!-- Filter Date Period Form -->
    <div class="glass-card mb-6">
        <form action="{{ route('reports.expenses') }}" method="GET" class="flex-between grid-2" style="align-items: flex-end;">
            <div style="display: flex; gap: 16px; flex: 1;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label" for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label" for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
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

    <!-- Summary Performance KPI Grid -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <div class="glass-card stat-card error" style="flex: 1;">
            <div class="stat-card-label">Total Nominal Pengeluaran</div>
            <div class="stat-card-value text-error">Rp {{ number_format($summary['total_expenses'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Total belanja operasional outlet</div>
        </div>

        <div class="glass-card stat-card warning" style="flex: 1;">
            <div class="stat-card-label">Jumlah Pencatatan (Kuitansi)</div>
            <div class="stat-card-value text-warning">{{ number_format($summary['transaction_count'] ?? 0, 0, ',', '.') }}</div>
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
                            $grandTotalExpenses = $summary['total_expenses'] ?: 1;
                        @endphp
                        @foreach($byCategory as $catName => $data)
                            @php
                                $catContribution = ($data['amount'] / $grandTotalExpenses) * 100;
                            @endphp
                            <tr>
                                <td><strong>{{ $catName }}</strong></td>
                                <td class="text-right"><strong>{{ $data['count'] }}</strong> kali</td>
                                <td class="text-right">
                                    <div style="text-align: right; margin-bottom: 4px;">
                                        <strong class="text-error">Rp {{ number_format($data['amount'], 0, ',', '.') }}</strong>
                                        <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;">({{ number_format($catContribution, 1) }}%)</span>
                                    </div>
                                    <!-- progress bar -->
                                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.06); border-radius: 99px; overflow: hidden;">
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
@endsection
