@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi Penjualan')

@section('content')
    <!-- Stat Card for Filtered Revenue -->
    <div class="stat-grid" style="grid-template-columns: 1fr;">
        <div class="glass-card stat-card cyan" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 32px;">
            <div>
                <div class="stat-card-label">Total Omzet Terfilter</div>
                <div class="stat-card-value text-cyan" style="font-size: 2.2rem; margin-bottom: 0;">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div class="brand-icon" style="background: rgba(6, 182, 212, 0.2); color: var(--cyan); width: 56px; height: 56px; border-radius: 50%;">
                <i data-lucide="line-chart" style="width: 28px; height: 28px;"></i>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="glass-card mb-6">
        <form action="{{ route('transactions.index') }}" method="GET" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="flex-between">
                <h4 style="font-weight: 700;">Filter Pencarian Transaksi</h4>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus-circle"></i>
                    <span>Buka POS Kasir</span>
                </a>
            </div>

            <div class="form-grid" style="align-items: flex-end;">
                <!-- Start Date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>

                <!-- End Date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>

                <!-- Payment Method -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="payment_method">Metode Pembayaran</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ ($filters['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="transfer" {{ ($filters['payment_method'] ?? '') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="qris" {{ ($filters['payment_method'] ?? '') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-secondary" style="flex: 1;">
                        <i data-lucide="filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary" title="Reset Filter">
                        <i data-lucide="refresh-cw"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions List Card -->
    <div class="glass-card">
        @if($transactions->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
                <i data-lucide="receipt-x" style="width: 64px; height: 64px; margin-bottom: 16px;"></i>
                <p>Tidak ada catatan transaksi ditemukan sesuai filter Anda.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Waktu & Tanggal</th>
                            <th>No. Invoice</th>
                            <th>Petugas Kasir</th>
                            <th>Metode Bayar</th>
                            <th class="text-right">Total Transaksi</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                            <tr>
                                <td>{{ $t->created_at->format('d M Y - H:i') }}</td>
                                <td><code>{{ $t->invoice_number }}</code></td>
                                <td><strong>{{ $t->user->name ?? 'Kasir' }}</strong></td>
                                <td>
                                    @if($t->payment_method === 'cash')
                                        <span class="badge badge-success">TUNAI</span>
                                    @elseif($t->payment_method === 'transfer')
                                        <span class="badge badge-info">TRANSFER</span>
                                    @else
                                        <span class="badge badge-warning">QRIS</span>
                                    @endif
                                </td>
                                <td class="text-right text-cyan font-bold">
                                    Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                                </td>
                                <td><small>{{ $t->note ?? '-' }}</small></td>
                                <td>
                                    <a href="{{ route('transactions.show', $t->id) }}" class="btn btn-secondary btn-sm" title="Lihat Struk Belanja">
                                        <i data-lucide="printer" style="width: 14px; height: 14px; margin-right: 4px;"></i>
                                        <span>Struk</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi
                </div>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
