@extends('layouts.app')

@section('title', 'Catat Pengeluaran')
@section('page_title', 'Pengeluaran Operasional Outlet')

@section('content')
    <!-- Stat Card for Filtered Expenses -->
    <div class="stat-grid" style="grid-template-columns: 1fr;">
        <div class="glass-card stat-card error" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 32px;">
            <div>
                <div class="stat-card-label">Total Pengeluaran Terfilter</div>
                <div class="stat-card-value text-error" style="font-size: 2.2rem; margin-bottom: 0;">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </div>
            </div>
            <div class="brand-icon" style="background: rgba(244, 63, 94, 0.2); color: var(--error); width: 56px; height: 56px; border-radius: 50%;">
                <i data-lucide="receipt" style="width: 28px; height: 28px;"></i>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="glass-card mb-6">
        <form action="{{ route('expenses.index') }}" method="GET" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="flex-between">
                <h4 style="font-weight: 700;">Filter Pencarian Pengeluaran</h4>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus-circle"></i>
                    <span>Catat Pengeluaran Baru</span>
                </a>
            </div>

            <div class="form-grid" style="align-items: flex-end;">
                <!-- Category filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="expense_category_id">Kategori Biaya</label>
                    <select name="expense_category_id" id="expense_category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['expense_category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

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

                <!-- Submit Button -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-secondary" style="flex: 1;">
                        <i data-lucide="filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary" title="Reset Filter">
                        <i data-lucide="refresh-cw"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Expenses Table Card -->
    <div class="glass-card">
        @if($expenses->isEmpty())
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
                <i data-lucide="receipt-x" style="width: 64px; height: 64px; margin-bottom: 16px;"></i>
                <p>Tidak ada catatan pengeluaran operasional ditemukan sesuai filter Anda.</p>
            </div>
        @else
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tanggal Operasional</th>
                            <th>Kategori Biaya</th>
                            <th>Deskripsi / Catatan</th>
                            <th>Petugas Pencatat</th>
                            <th>Bukti Nota</th>
                            <th class="text-right">Jumlah Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $e)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge badge-warning">{{ $e->expenseCategory->name ?? 'Lainnya' }}</span>
                                </td>
                                <td><strong>{{ $e->description ?? '-' }}</strong></td>
                                <td><small>{{ $e->user->name ?? 'System' }}</small></td>
                                <td>
                                    @if($e->receipt_image)
                                        <a href="{{ asset('storage/' . $e->receipt_image) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; border-color: var(--cyan); color: var(--cyan);">
                                            <i data-lucide="image" style="width: 12px; height: 12px; margin-right: 4px; vertical-align: middle;"></i>
                                            <span>Lihat Nota</span>
                                        </a>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted)">Tanpa Lampiran</span>
                                    @endif
                                </td>
                                <td class="text-right text-error font-bold">
                                    Rp {{ number_format($e->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('expenses.edit', $e->id) }}" class="btn btn-secondary btn-sm" title="Edit Pengeluaran">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        
                                        <form action="{{ route('expenses.destroy', $e->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengeluaran ini?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i data-lucide="trash" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Menampilkan {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} dari {{ $expenses->total() }} pengeluaran
                </div>
                <div>
                    {{ $expenses->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
