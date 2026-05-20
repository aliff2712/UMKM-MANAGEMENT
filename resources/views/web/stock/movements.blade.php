@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')
@section('page_title', 'Audit Log & Riwayat Stok')

@section('content')
    <!-- Filters Bar -->
    <div class="glass-card mb-6">
        <form action="{{ route('stock.movements') }}" method="GET" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="flex-between">
                <h4 style="font-weight: 700;">Filter Riwayat Pergerakan</h4>
                <a href="{{ route('stock.adjust') }}" class="btn btn-primary">
                    <i data-lucide="plus-circle"></i>
                    <span>Koreksi Stok Manual</span>
                </a>
            </div>

            <div class="form-grid" style="align-items: flex-end;">
                <!-- Product select -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="product_id">Pilih Produk</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="">Semua Produk</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ ($filters['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }} [{{ $p->sku }}]</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type select -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="type">Tipe Pergerakan</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">Semua Tipe</option>
                        <option value="in" {{ ($filters['type'] ?? '') == 'in' ? 'selected' : '' }}>Stok Masuk (+)</option>
                        <option value="out" {{ ($filters['type'] ?? '') == 'out' ? 'selected' : '' }}>Stok Keluar (-)</option>
                        <option value="adjustment" {{ ($filters['type'] ?? '') == 'adjustment' ? 'selected' : '' }}>Penyesuaian / Koreksi</option>
                    </select>
                </div>

                <!-- Period start date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>

                <!-- Period end date -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="submit" class="btn btn-secondary">
                    <i data-lucide="filter"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('stock.movements') }}" class="btn btn-secondary" title="Reset Filter">
                    <i data-lucide="refresh-cw"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Movements List Card -->
    <div class="glass-card">
        @if($movements->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
                <i data-lucide="arrow-left-right" style="width: 64px; height: 64px; margin-bottom: 16px;"></i>
                <p>Belum ada catatan pergerakan stok ditemukan sesuai filter Anda.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Waktu & Tanggal</th>
                            <th>Produk</th>
                            <th>Petugas</th>
                            <th>Jenis Perubahan</th>
                            <th class="text-right">Jumlah (Quantity)</th>
                            <th>Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $move)
                            <tr>
                                <td>{{ $move->created_at->format('d M Y - H:i') }}</td>
                                <td>
                                    <strong>{{ $move->product->name ?? 'Produk Dihapus' }}</strong><br>
                                    <code>{{ $move->product->sku ?? '-' }}</code>
                                </td>
                                <td><strong>{{ $move->user->name ?? 'System' }}</strong></td>
                                <td>
                                    @if($move->type === 'in')
                                        <span class="badge badge-success">Stok Masuk</span>
                                    @elseif($move->type === 'out')
                                        <span class="badge badge-info">Stok Keluar</span>
                                    @else
                                        <span class="badge badge-warning">Penyesuaian</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold {{ $move->type === 'in' ? 'text-success' : ($move->type === 'out' ? 'text-cyan' : 'text-warning') }}">
                                    {{ $move->type === 'in' ? '+' : ($move->type === 'out' ? '-' : '') }}{{ $move->qty }}
                                </td>
                                <td><small>{{ $move->note ?? '-' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} riwayat
                </div>
                <div>
                    {{ $movements->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
