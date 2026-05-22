@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')
@section('page_title', 'Laporan Laba & Rugi Bulanan')

@section('content')

@php $isProfit = ($status ?? 'loss') === 'profit'; @endphp

{{-- ── Period Filter ────────────────────────────────────────────────── --}}
<div class="pl-filter-card">
    <form action="{{ route('reports.profit-loss') }}" method="GET" class="pl-filter-form" id="filter-form">
        <div class="pl-filter-fields">
            <div class="pl-field">
                <label class="pl-label" for="month">Bulan</label>
                <select name="month" id="month" class="pl-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="pl-field">
                <label class="pl-label" for="year">Tahun</label>
                <select name="year" id="year" class="pl-select">
                    @for($y = date('Y') - 5; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="pl-filter-actions">
            <button type="submit" class="pl-btn-primary">
                <i data-lucide="filter" style="width:15px;height:15px;"></i>
                Terapkan
            </button>
            <button type="button" class="pl-btn-success" onclick="exportReport('csv')">
                <i data-lucide="download" style="width:15px;height:15px;"></i>
                Ekspor CSV
            </button>
            <button type="button" class="pl-btn-info" onclick="exportReport('excel')">
                <i data-lucide="file-text" style="width:15px;height:15px;"></i>
                Ekspor Excel
            </button>
            <a href="{{ route('dashboard') }}" class="pl-btn-ghost">
                <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
                Kembali
            </a>
        </div>
    </form>

    <form id="export-form" action="{{ route('reports.profit-loss.export') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="format" id="export-format" value="csv">
    </form>
</div>

{{-- ── Summary KPI Cards ────────────────────────────────────────────── --}}
<div class="pl-kpi-grid">
    <div class="pl-kpi pl-kpi-income">
        <div class="pl-kpi-icon"><i data-lucide="trending-up"></i></div>
        <div class="pl-kpi-body">
            <span class="pl-kpi-label">Total Pendapatan</span>
            {{-- FIX: $income adalah array, akses key net_revenue --}}
            <span class="pl-kpi-value">Rp {{ number_format($income['net_revenue'] ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="pl-kpi pl-kpi-cogs">
        <div class="pl-kpi-icon"><i data-lucide="package"></i></div>
        <div class="pl-kpi-body">
            <span class="pl-kpi-label">HPP</span>
            <span class="pl-kpi-value">Rp {{ number_format($cogs ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="pl-kpi pl-kpi-gross">
        <div class="pl-kpi-icon"><i data-lucide="bar-chart-2"></i></div>
        <div class="pl-kpi-body">
            <span class="pl-kpi-label">Laba Kotor</span>
            <span class="pl-kpi-value">Rp {{ number_format($grossProfit ?? 0, 0, ',', '.') }}</span>
            <span class="pl-kpi-sub">Margin {{ number_format($grossMargin ?? 0, 1) }}%</span>
        </div>
    </div>
    <div class="pl-kpi {{ $isProfit ? 'pl-kpi-profit' : 'pl-kpi-loss' }}">
        <div class="pl-kpi-icon">
            <i data-lucide="{{ $isProfit ? 'circle-check' : 'circle-x' }}"></i>
        </div>
        <div class="pl-kpi-body">
            <span class="pl-kpi-label">Laba Bersih</span>
            <span class="pl-kpi-value">Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</span>
            <span class="pl-kpi-sub">Margin {{ number_format($netMargin ?? 0, 1) }}%</span>
        </div>
    </div>
</div>

{{-- ── Main Statement ───────────────────────────────────────────────── --}}
<div class="pl-statement">

    {{-- Header --}}
    <div class="pl-stmt-header">
        <div class="pl-stmt-logo">
            <i data-lucide="file-bar-chart-2"></i>
        </div>
        <div class="pl-stmt-title-block">
            <h2 class="pl-stmt-title">Laporan Laba Rugi Komprehensif</h2>
            <p class="pl-stmt-period">
                Periode: {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                &nbsp;·&nbsp;
                {{ auth()->user()->outlet->name ?? 'Outlet Utama' }}
            </p>
        </div>
        <span class="pl-status-badge {{ $isProfit ? 'pl-badge-profit' : 'pl-badge-loss' }}">
            <i data-lucide="{{ $isProfit ? 'trending-up' : 'trending-down' }}" style="width:13px;height:13px;"></i>
            {{ $isProfit ? 'Profit' : 'Rugi' }}
        </span>
    </div>

    {{-- Statement body --}}
    <div class="pl-stmt-body">

        {{-- 1. Revenue --}}
        <div class="pl-section">
            <div class="pl-section-title pl-sect-income">
                <span class="pl-sect-num">1</span>
                <span>Pendapatan Usaha <em>(Revenue)</em></span>
            </div>
            {{-- FIX: gross_revenue untuk baris detail (sebelum diskon) --}}
            <div class="pl-row">
                <span class="pl-row-label">Penerimaan Kotor Penjualan Barang</span>
                <span class="pl-row-val pl-val-pos">+Rp {{ number_format($income['gross_revenue'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="pl-row">
                <span class="pl-row-label">Diskon</span>
                <span class="pl-row-val pl-val-neg">-Rp {{ number_format($income['total_discount'] ?? 0, 0, ',', '.') }}</span>
            </div>
            {{-- FIX: net_revenue untuk subtotal (setelah diskon) --}}
            <div class="pl-subtotal pl-sub-income">
                <span>Total Pendapatan Operasional</span>
                <span>Rp {{ number_format($income['net_revenue'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 2. COGS --}}
        <div class="pl-section">
            <div class="pl-section-title pl-sect-cogs">
                <span class="pl-sect-num">2</span>
                <span>Harga Pokok Penjualan <em>(COGS)</em></span>
            </div>
            <div class="pl-row">
                <span class="pl-row-label">Harga Pokok Persediaan Barang Terjual</span>
                <span class="pl-row-val pl-val-neg">-Rp {{ number_format($cogs ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="pl-subtotal pl-sub-neg">
                <span>Total HPP</span>
                <span>Rp {{ number_format($cogs ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 3. Gross Profit --}}
        <div class="pl-gross-row">
            <div class="pl-gross-left">
                <span class="pl-sect-num pl-num-gross">3</span>
                <span class="pl-gross-label">Laba Kotor Outlet <em>(Gross Profit)</em></span>
            </div>
            <div class="pl-gross-right">
                <span class="pl-gross-amount">Rp {{ number_format($grossProfit ?? 0, 0, ',', '.') }}</span>
                <span class="pl-gross-margin">Margin: {{ number_format($grossMargin ?? 0, 1) }}%</span>
            </div>
        </div>

        {{-- 4. OpEx --}}
        <div class="pl-section">
            <div class="pl-section-title pl-sect-opex">
                <span class="pl-sect-num">4</span>
                <span>Beban Operasional <em>(OpEx)</em></span>
            </div>
            {{-- FIX: $expenses adalah array, akses key total --}}
            <div class="pl-row">
                <span class="pl-row-label">Total Belanja Kas Operasional &amp; Biaya Outlet</span>
                <span class="pl-row-val pl-val-neg">-Rp {{ number_format($expenses['total'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="pl-subtotal pl-sub-neg">
                <span>Total Biaya Operasional</span>
                <span>Rp {{ number_format($expenses['total'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 5. Net Profit --}}
        <div class="pl-net-row {{ $isProfit ? 'pl-net-profit' : 'pl-net-loss' }}">
            <div class="pl-net-left">
                <span class="pl-sect-num {{ $isProfit ? 'pl-num-profit' : 'pl-num-loss' }}">5</span>
                <span class="pl-net-label">Laba Bersih Outlet <em>(Net Profit / Loss)</em></span>
            </div>
            <div class="pl-net-right">
                <span class="pl-net-amount {{ $isProfit ? 'pl-col-profit' : 'pl-col-loss' }}">
                    Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}
                </span>
                <span class="pl-net-margin">Margin: {{ number_format($netMargin ?? 0, 1) }}%</span>
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="pl-stmt-footer">
        <i data-lucide="shield-check" style="width:13px;height:13px;margin-right:5px;vertical-align:-2px;opacity:.5;"></i>
        Laporan kualifikasi internal outlet · Dibuat {{ date('d F Y') }}
    </div>

</div>

@endsection


@section('scripts')
<style>
/* ── Filter Card ────────────────────────────────────────────────────── */
.pl-filter-card {
    background: var(--glass-bg,#fff);
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 16px;
    padding: 18px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
}
.pl-filter-form {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}
.pl-filter-fields { display: flex; gap: 12px; flex: 1; min-width: 0; flex-wrap: wrap; }
.pl-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 120px; }
.pl-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted,#6b7280); }
.pl-select {
    height: 38px;
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 10px;
    padding: 0 12px;
    font-size: 13.5px;
    background: var(--glass-secondary,#f9fafb);
    color: var(--text-primary,#111827);
    outline: none;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
}
.pl-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.13); background-color: #fff; }
.pl-filter-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }

.pl-btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    height: 38px; padding: 0 16px;
    background: linear-gradient(135deg,#6366f1,#818cf8);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: box-shadow .15s, transform .1s;
    box-shadow: 0 3px 12px rgba(99,102,241,.3);
    font-family: inherit;
}
.pl-btn-primary:hover  { box-shadow: 0 5px 18px rgba(99,102,241,.4); transform: translateY(-1px); }
.pl-btn-primary:active { transform: scale(.97); }

.pl-btn-ghost {
    display: inline-flex; align-items: center; gap: 6px;
    height: 38px; padding: 0 14px;
    background: var(--glass-secondary,#f3f4f6);
    color: var(--text-secondary,#374151);
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 10px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: background .15s, border-color .15s;
    text-decoration: none;
}
.pl-btn-ghost:hover { background: var(--glass-bg,#fff); border-color: #9ca3af; }

.pl-btn-success {
    display: inline-flex; align-items: center; gap: 6px;
    height: 38px; padding: 0 16px;
    background: linear-gradient(135deg,#10b981,#34d399);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: box-shadow .15s, transform .1s;
    box-shadow: 0 3px 12px rgba(16,185,129,.3);
    font-family: inherit;
}
.pl-btn-success:hover  { box-shadow: 0 5px 18px rgba(16,185,129,.4); transform: translateY(-1px); }
.pl-btn-success:active { transform: scale(.97); }

.pl-btn-info {
    display: inline-flex; align-items: center; gap: 6px;
    height: 38px; padding: 0 16px;
    background: linear-gradient(135deg,#3b82f6,#60a5fa);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: box-shadow .15s, transform .1s;
    box-shadow: 0 3px 12px rgba(59,130,246,.3);
    font-family: inherit;
}
.pl-btn-info:hover  { box-shadow: 0 5px 18px rgba(59,130,246,.4); transform: translateY(-1px); }
.pl-btn-info:active { transform: scale(.97); }

/* ── KPI Grid ───────────────────────────────────────────────────────── */
.pl-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.pl-kpi {
    background: var(--glass-bg,#fff);
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 14px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform .15s, box-shadow .15s;
    animation: pl-fade-up .35s ease both;
}
.pl-kpi:nth-child(1){ animation-delay:.05s; }
.pl-kpi:nth-child(2){ animation-delay:.1s; }
.pl-kpi:nth-child(3){ animation-delay:.15s; }
.pl-kpi:nth-child(4){ animation-delay:.2s; }
@keyframes pl-fade-up {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}
.pl-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.07); }

.pl-kpi-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pl-kpi-icon svg { width: 20px; height: 20px; }

.pl-kpi-income .pl-kpi-icon { background: rgba(34,197,94,.12); color: #16a34a; }
.pl-kpi-cogs   .pl-kpi-icon { background: rgba(239,68,68,.1);  color: #dc2626; }
.pl-kpi-gross  .pl-kpi-icon { background: rgba(99,102,241,.12); color: #6366f1; }
.pl-kpi-profit .pl-kpi-icon { background: rgba(16,185,129,.12); color: #059669; }
.pl-kpi-loss   .pl-kpi-icon { background: rgba(239,68,68,.12);  color: #dc2626; }

.pl-kpi-income { border-left: 3px solid #22c55e; }
.pl-kpi-cogs   { border-left: 3px solid #ef4444; }
.pl-kpi-gross  { border-left: 3px solid #6366f1; }
.pl-kpi-profit { border-left: 3px solid #10b981; }
.pl-kpi-loss   { border-left: 3px solid #ef4444; }

.pl-kpi-body { display: flex; flex-direction: column; min-width: 0; }
.pl-kpi-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted,#6b7280); margin-bottom: 3px; }
.pl-kpi-value { font-size: 15px; font-weight: 700; color: var(--text-primary,#111827); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pl-kpi-sub   { font-size: 11px; color: var(--text-muted,#9ca3af); margin-top: 2px; }

/* ── Statement Card ─────────────────────────────────────────────────── */
.pl-statement {
    background: var(--glass-bg,#fff);
    border: 1px solid var(--border-color,#e5e7eb);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
    animation: pl-fade-up .4s .25s ease both;
}

/* Header */
.pl-stmt-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid var(--border-color,#e5e7eb);
    background: var(--glass-secondary,#f9fafb);
    flex-wrap: wrap;
}
.pl-stmt-logo {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg,#6366f1,#818cf8);
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
}
.pl-stmt-logo svg { width: 22px; height: 22px; }
.pl-stmt-title-block { flex: 1; min-width: 0; }
.pl-stmt-title { font-size: 16px; font-weight: 700; color: var(--text-primary,#111827); margin: 0 0 3px; }
.pl-stmt-period { font-size: 12px; color: var(--text-muted,#6b7280); margin: 0; }

.pl-status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 99px;
    font-size: 12px; font-weight: 600;
}
.pl-badge-profit { background: rgba(16,185,129,.12); color: #059669; border: 1px solid rgba(16,185,129,.25); }
.pl-badge-loss   { background: rgba(239,68,68,.1);   color: #dc2626; border: 1px solid rgba(239,68,68,.2); }

/* Body */
.pl-stmt-body { padding: 8px 0; }

/* Section */
.pl-section { padding: 4px 0 8px; border-bottom: 1px solid var(--border-color,#f0f0f0); margin: 0 24px; }
.pl-section:last-of-type { border-bottom: none; }

.pl-section-title {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 0 8px;
    font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
}
.pl-sect-income { color: #16a34a; }
.pl-sect-cogs   { color: #dc2626; }
.pl-sect-opex   { color: #d97706; }

.pl-sect-num {
    width: 22px; height: 22px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.pl-sect-income .pl-sect-num { background: #22c55e; }
.pl-sect-cogs   .pl-sect-num { background: #ef4444; }
.pl-sect-opex   .pl-sect-num { background: #f59e0b; }

.pl-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 0 7px 32px;
    font-size: 13.5px;
}
.pl-row-label { color: var(--text-secondary,#374151); }
.pl-row-val { font-weight: 500; font-size: 13.5px; }
.pl-val-pos { color: #16a34a; }
.pl-val-neg { color: #dc2626; }

.pl-subtotal {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0 8px 32px;
    font-size: 13px; font-weight: 700;
    border-radius: 8px; margin: 4px 0;
    background: var(--glass-secondary,#f9fafb);
    padding-left: 32px; padding-right: 0;
}
.pl-sub-income { color: #16a34a; }
.pl-sub-neg    { color: #dc2626; }

/* Gross profit highlight row */
.pl-gross-row {
    display: flex; align-items: center; justify-content: space-between;
    margin: 12px 24px;
    padding: 14px 18px;
    background: rgba(99,102,241,.07);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 12px;
    gap: 12px;
    flex-wrap: wrap;
}
.pl-gross-left { display: flex; align-items: center; gap: 10px; }
.pl-num-gross { background: #6366f1; }
.pl-gross-label { font-size: 13.5px; font-weight: 700; color: var(--text-primary,#111827); }
.pl-gross-label em { font-style: normal; font-weight: 400; color: var(--text-muted,#6b7280); font-size: 12px; }
.pl-gross-right { display: flex; flex-direction: column; align-items: flex-end; }
.pl-gross-amount { font-size: 17px; font-weight: 800; color: #6366f1; }
.pl-gross-margin { font-size: 11px; color: var(--text-muted,#9ca3af); margin-top: 2px; }

/* Net profit highlight row */
.pl-net-row {
    display: flex; align-items: center; justify-content: space-between;
    margin: 0 24px 8px;
    padding: 18px 20px;
    border-radius: 14px;
    gap: 12px;
    flex-wrap: wrap;
    border: 2px solid transparent;
}
.pl-net-profit { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.3); }
.pl-net-loss   { background: rgba(239,68,68,.07);  border-color: rgba(239,68,68,.25); }

.pl-net-left { display: flex; align-items: center; gap: 10px; }
.pl-num-profit { background: #10b981; }
.pl-num-loss   { background: #ef4444; }
.pl-net-label { font-size: 14px; font-weight: 700; color: var(--text-primary,#111827); }
.pl-net-label em { font-style: normal; font-weight: 400; color: var(--text-muted,#6b7280); font-size: 12px; }
.pl-net-right { display: flex; flex-direction: column; align-items: flex-end; }
.pl-net-amount { font-size: 20px; font-weight: 800; }
.pl-col-profit { color: #059669; }
.pl-col-loss   { color: #dc2626; }
.pl-net-margin { font-size: 11px; color: var(--text-muted,#9ca3af); margin-top: 3px; }

/* Footer */
.pl-stmt-footer {
    padding: 14px 24px;
    border-top: 1px dashed var(--border-color,#e5e7eb);
    font-size: 11.5px;
    color: var(--text-muted,#9ca3af);
    text-align: center;
    background: var(--glass-secondary,#f9fafb);
}


</style>

<script>
function exportReport(format) {
    const form = document.getElementById('export-form');
    const month = document.getElementById('month').value;
    const year = document.getElementById('year').value;
    
    form.querySelector('input[name="month"]').value = month;
    form.querySelector('input[name="year"]').value = year;
    form.querySelector('#export-format').value = format;
    
    form.submit();
}
</script>
@endsection