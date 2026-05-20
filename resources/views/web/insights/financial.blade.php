@extends('layouts.app')

@section('title', 'Insight Finansial')
@section('page_title', 'Insight Keuangan & Arus Kas')

@section('content')
    <!-- Filter Period Form -->
    <div class="glass-card mb-6">
        <form action="{{ route('insights.financial') }}" method="GET" class="flex-between grid-2" style="align-items: flex-end;">
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

    <!-- Stat Grid -->
    <div class="stat-grid">
        <div class="glass-card stat-card cyan">
            <div class="stat-card-label">Total Pendapatan (Omzet)</div>
            <div class="stat-card-value text-cyan">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Total transaksi penjualan bruto</div>
        </div>

        <div class="glass-card stat-card error">
            <div class="stat-card-label">Total Pengeluaran</div>
            <div class="stat-card-value text-error">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
            <div class="stat-card-desc">Operasional & belanja bahan baku</div>
        </div>

        @php
            $isProfit = $status === 'profit';
        @endphp
        <div class="glass-card stat-card {{ $isProfit ? 'success' : 'error' }}">
            <div class="stat-card-label">Laba Bersih</div>
            <div class="stat-card-value {{ $isProfit ? 'text-success' : 'text-error' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </div>
            <div class="stat-card-desc">Status bisnis: {{ strtoupper($status) }}</div>
        </div>

        <div class="glass-card stat-card primary">
            <div class="stat-card-label">Margin Keuntungan</div>
            <div class="stat-card-value text-primary">{{ number_format($profitMargin, 1, ',', '.') }}%</div>
            <div class="stat-card-desc">Rasio margin laba bersih terhadap omzet</div>
        </div>
    </div>

    <!-- Financial Analysis details -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-primary">
                <i data-lucide="wallet"></i>
                <span>Analisis Arus Kas & Kesehatan Finansial</span>
            </h3>
        </div>

        <div class="grid-2">
            <div>
                <h4 class="mb-4">Rasio Beban Operasional</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5;" class="mb-4">
                    Rasio beban menggambarkan seberapa besar pendapatan kotor Anda dikonsumsi oleh biaya operasional. Batas ideal rasio beban adalah di bawah 70%.
                </p>

                @php
                    $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;
                    $expenseRatio = min(100, $expenseRatio);
                @endphp
                <div style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: var(--border-radius-md); border: 1px solid var(--glass-border);">
                    <div class="flex-between mb-4">
                        <span>Rasio Pengeluaran vs Omzet:</span>
                        <strong class="{{ $expenseRatio > 70 ? 'text-error' : 'text-success' }}">{{ number_format($expenseRatio, 1) }}%</strong>
                    </div>
                    <div style="width: 100%; height: 10px; background: rgba(255,255,255,0.1); border-radius: 99px; overflow: hidden;">
                        <div style="width: {{ $expenseRatio }}%; height: 100%; background: {{ $expenseRatio > 70 ? 'var(--error)' : 'var(--success)' }}; border-radius: 99px;"></div>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="mb-4">Rekomendasi Strategis AI</h4>
                <div style="background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.2); padding: 16px; border-radius: var(--border-radius-md); display: flex; flex-direction: column; gap: 12px;">
                    @if($isProfit)
                        <div style="display: flex; gap: 8px;">
                            <i data-lucide="trending-up" class="text-success" style="flex-shrink: 0; margin-top: 2px;"></i>
                            <p style="font-size: 0.85rem; line-height: 1.4;">
                                <strong>Performa Bagus!</strong> Arus kas Anda positif dengan margin bersih sebesar {{ number_format($profitMargin, 1) }}%. Anda dapat memikirkan ekspansi stok produk terlaris atau alokasikan dana cadangan.
                            </p>
                        </div>
                    @else
                        <div style="display: flex; gap: 8px;">
                            <i data-lucide="alert-circle" class="text-error" style="flex-shrink: 0; margin-top: 2px;"></i>
                            <p style="font-size: 0.85rem; line-height: 1.4;">
                                <strong>Peringatan Defisit!</strong> Pengeluaran operasional melampaui omzet penjualan. Disarankan untuk meninjau detail pengeluaran bulanan dan menunda biaya yang tidak mendesak.
                            </p>
                        </div>
                    @endif
                    <div style="display: flex; gap: 8px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 8px;">
                        <i data-lucide="lightbulb" class="text-cyan" style="flex-shrink: 0; margin-top: 2px;"></i>
                        <p style="font-size: 0.85rem; line-height: 1.4;">
                            Bandingkan performa detail pengeluaran di menu <strong>Laporan Pengeluaran</strong> untuk menghemat anggaran operasional.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
