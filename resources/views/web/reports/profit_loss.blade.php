@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')
@section('page_title', 'Laporan Laba & Rugi Bulanan')

@section('content')
<!-- Period Filter Form -->
<div class="glass-card mb-6">
    <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex-between grid-2" style="align-items: flex-end;">
        <div style="display: flex; gap: 16px; flex: 1;">
            <!-- Month Selection -->
            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label class="form-label" for="month">Pilih Bulan</label>
                <select name="month" id="month" class="form-control">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                        @endfor
                </select>
            </div>
            <!-- Year Selection -->
            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label class="form-label" for="year">Pilih Tahun</label>
                <select name="year" id="year" class="form-control">
                    @for($y = date('Y') - 5; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                </select>
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

<!-- Professional Accounting Statement Table -->
<div class="glass-card" style="max-width: 800px; margin: 0 auto;">
    <div class="glass-card-header" style="text-align: center; flex-direction: column; align-items: center; border-bottom: 2px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h2 style="font-weight: 800; font-size: 1.35rem; margin-bottom: 4px;">LAPORAN LABA RUGI KOMPREHENSIF</h2>
        <div style="font-size: 0.85rem; color: var(--text-cyan); font-weight: bold; letter-spacing: 0.5px;">
            PERIODE: {{ strtoupper(\Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y')) }}
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">{{ auth()->user()->outlet->name ?? 'Outlet Utama' }}</div>
    </div>

    <div class="table-container">
        <table class="custom-table" style="font-size: 0.95rem;">
            <tbody>
                <tr style="background: rgba(255,255,255,0.02);">
                    <td colspan="2"><strong class="text-cyan">1. PENDAPATAN USAHA (REVENUE)</strong></td>
                </tr>
                <tr>
                    <td style="padding-left: 32px; color: var(--text-muted);">Penerimaan Kotor Penjualan Barang</td>
                    {{-- Menggunakan is_numeric untuk memastikan data adalah angka --}}
                    <td class="text-right text-success">+Rp {{ number_format(is_numeric($income) ? $income : 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <td style="padding-left: 32px; font-weight: bold;">TOTAL PENDAPATAN OPERASIONAL</td>
                    <td class="text-right font-bold text-success">Rp {{ number_format(is_numeric($income) ? $income : 0, 0, ',', '.') }}</td>
                </tr>

                <tr style="background: rgba(255,255,255,0.02);">
                    <td colspan="2"><strong class="text-primary">2. HARGA POKOK PENJUALAN (COGS)</strong></td>
                </tr>
                <tr>
                    <td style="padding-left: 32px; color: var(--text-muted);">Harga Pokok Persediaan Barang Terjual</td>
                    <td class="text-right text-error">-Rp {{ number_format(is_numeric($cogs) ? $cogs : 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <td style="padding-left: 32px; font-weight: bold;">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                    <td class="text-right font-bold text-error">Rp {{ number_format(is_numeric($cogs) ? $cogs : 0, 0, ',', '.') }}</td>
                </tr>

                <tr style="background: rgba(255,255,255,0.04); border-bottom: 2px solid var(--glass-border); font-size: 1.05rem;">
                    <td><strong>3. LABA KOTOR OUTLET (GROSS PROFIT)</strong></td>
                    <td class="text-right font-bold text-cyan">
                        Rp {{ number_format(is_numeric($grossProfit) ? $grossProfit : 0, 0, ',', '.') }}
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-left: 6px;">
                            (Margin: {{ number_format(is_numeric($grossMargin) ? $grossMargin : 0, 1) }}%)
                        </span>
                    </td>
                </tr>

                <tr style="background: rgba(255,255,255,0.02);">
                    <td colspan="2"><strong class="text-warning">4. BEBAN OPERASIONAL (OPEX)</strong></td>
                </tr>
                <tr>
                    <td style="padding-left: 32px; color: var(--text-muted);">Total Belanja Kas Operasional & Biaya Outlet</td>
                    <td class="text-right text-error">-Rp {{ number_format(is_numeric($expenses) ? $expenses : 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <td style="padding-left: 32px; font-weight: bold;">TOTAL BIAYA OPERASIONAL (BIAYA KAS)</td>
                    <td class="text-right font-bold text-error">Rp {{ number_format(is_numeric($expenses) ? $expenses : 0, 0, ',', '.') }}</td>
                </tr>

                @php $isProfit = ($status ?? 'loss') === 'profit'; @endphp
                <tr style="background: rgba(139, 92, 246, 0.12); font-size: 1.15rem; border: 2px solid {{ $isProfit ? 'var(--success)' : 'var(--error)' }};">
                    <td><strong>5. LABA BERSIH OUTLET (NET PROFIT / LOSS)</strong></td>
                    <td class="text-right font-bold {{ $isProfit ? 'text-success' : 'text-error' }}">
                        Rp {{ number_format(is_numeric($netProfit) ? $netProfit : 0, 0, ',', '.') }}
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal; margin-left: 6px;">
                            (Margin: {{ number_format(is_numeric($netMargin) ? $netMargin : 0, 1) }}%)
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); padding: 14px; border-radius: var(--border-radius-md); text-align: center; margin-top: 24px; font-size: 0.8rem; color: var(--text-muted);">
        Laporan kualifikasi internal outlet dibuat secara sistematis per {{ date('d F Y') }}.
    </div>
</div>
@endsection