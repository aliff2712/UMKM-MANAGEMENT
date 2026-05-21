@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')
@section('page_title', 'Audit Log & Riwayat Stok')

@section('content')

    {{-- ── Filter Card ── --}}
    <div class="glass-card mb-6">
        <form action="{{ route('stock.movements') }}" method="GET">

            <div class="flex-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <h4 style="font-weight: 700; font-size: 1rem; color: var(--text-main);">Filter Riwayat Pergerakan</h4>
                <a href="{{ route('stock.adjust') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus-circle"></i>
                    <span>Koreksi Stok</span>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="product_id">Produk</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="">Semua Produk</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ ($filters['product_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} [{{ $p->sku }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="type">Tipe Pergerakan</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">Semua Tipe</option>
                        <option value="in"         {{ ($filters['type'] ?? '') == 'in'         ? 'selected' : '' }}>Stok Masuk (+)</option>
                        <option value="out"        {{ ($filters['type'] ?? '') == 'out'        ? 'selected' : '' }}>Stok Keluar (-)</option>
                        <option value="adjustment" {{ ($filters['type'] ?? '') == 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ $filters['start_date'] ?? '' }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ $filters['end_date'] ?? '' }}">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                <a href="{{ route('stock.movements') }}" class="btn btn-secondary btn-sm" title="Reset">
                    <i data-lucide="refresh-cw"></i>
                    <span>Reset</span>
                </a>
                <button type="submit" class="btn btn-secondary btn-sm">
                    <i data-lucide="filter"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ── Movements Table ── --}}
    <div class="glass-card">
        @if($movements->isEmpty())
            <div style="text-align: center; padding: 64px 24px; color: var(--text-muted);">
                <i data-lucide="arrow-left-right" style="width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.4;"></i>
                <p style="font-size: 0.9rem;">Belum ada catatan pergerakan stok sesuai filter Anda.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Produk</th>
                            <th class="hide-mobile">Petugas</th>
                            <th>Tipe</th>
                            <th class="text-right">Qty</th>
                            <th class="hide-mobile">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $move)
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.82rem; color: var(--text-muted);">
                                    {{ $move->created_at->format('d M Y') }}<br>
                                    <strong style="color: var(--text-dark);">{{ $move->created_at->format('H:i') }}</strong>
                                </td>
                                <td>
                                    <strong style="font-size: 0.875rem;">{{ $move->product->name ?? 'Produk Dihapus' }}</strong><br>
                                    <code style="font-size: 0.72rem; color: var(--text-muted);">{{ $move->product->sku ?? '-' }}</code>
                                    {{-- mobile: tampilkan petugas & catatan inline --}}
                                    <div class="show-mobile-only" style="display: none; margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $move->user->name ?? 'System' }}
                                        @if($move->note)
                                            · {{ $move->note }}
                                        @endif
                                    </div>
                                </td>
                                <td class="hide-mobile" style="font-size: 0.875rem;">
                                    <strong>{{ $move->user->name ?? 'System' }}</strong>
                                </td>
                                <td>
                                    @if($move->type === 'in')
                                        <span class="badge badge-success">Masuk</span>
                                    @elseif($move->type === 'out')
                                        <span class="badge badge-info">Keluar</span>
                                    @else
                                        <span class="badge badge-warning">Koreksi</span>
                                    @endif
                                </td>
                                <td class="text-right" style="font-weight: 700; font-size: 0.95rem;
                                    color: {{ $move->type === 'in' ? 'var(--success)' : ($move->type === 'out' ? 'var(--cyan)' : 'var(--warning)') }};">
                                    {{ $move->type === 'in' ? '+' : ($move->type === 'out' ? '-' : '±') }}{{ $move->qty }}
                                </td>
                                <td class="hide-mobile" style="font-size: 0.82rem; color: var(--text-muted);">
                                    {{ $move->note ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; flex-wrap: wrap; gap: 12px;">
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $movements->firstItem() ?? 0 }}–{{ $movements->lastItem() ?? 0 }}
                    dari {{ $movements->total() }} riwayat
                </span>
                <div>{{ $movements->links() }}</div>
            </div>
        @endif
    </div>

@endsection

@section('styles')
<style>
    @media (max-width: 768px) {
        .hide-mobile { display: none !important; }
        .show-mobile-only { display: block !important; }
    }
</style>
@endsection