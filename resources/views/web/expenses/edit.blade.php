@extends('layouts.app')

@section('title', 'Edit Pengeluaran')
@section('page_title', 'Edit Catatan Pengeluaran')

@section('content')
    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-accent">
                <i data-lucide="edit-3"></i>
                <span>Edit Pengeluaran: Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
            </h3>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">Batal</a>
        </div>

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            @method('PUT')
            
            <!-- Hidden Outlet ID -->
            <input type="hidden" name="outlet_id" value="{{ $expense->outlet_id }}">

            <!-- Expense Category -->
            <div class="form-group">
                <label class="form-label" for="expense_category_id">Kategori Biaya *</label>
                <select name="expense_category_id" id="expense_category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori Biaya --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Amount -->
            <div class="form-group">
                <label class="form-label" for="amount">Jumlah Biaya / Nominal (Rp) *</label>
                <input type="number" name="amount" id="amount" class="form-control" placeholder="0" min="0" value="{{ old('amount', $expense->amount) }}" required>
            </div>

            <!-- Expense Date -->
            <div class="form-group">
                <label class="form-label" for="expense_date">Tanggal Pengeluaran *</label>
                <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date) }}" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">Deskripsi / Detail Biaya</label>
                <textarea name="description" id="description" class="form-control" placeholder="Masukan deskripsi pengeluaran...Override">{{ old('description', $expense->description) }}</textarea>
            </div>

            <!-- Current Attachment Preview -->
            @if($expense->receipt_image)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 12px; border-radius: var(--border-radius-sm); margin-bottom: 20px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">Nota Terlampir Sekarang:</div>
                    <a href="{{ asset('storage/' . $expense->receipt_image) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem;" class="text-cyan">
                        <i data-lucide="image" style="width: 14px; height: 14px;"></i>
                        <span>Buka dan Lihat Nota Saat Ini</span>
                    </a>
                </div>
            @endif

            <!-- Receipt Image Attachment Upload -->
            <div class="form-group mb-6">
                <label class="form-label" for="receipt_image">Unggah Ulang / Ganti Foto Nota</label>
                <input type="file" name="receipt_image" id="receipt_image" class="form-control" accept="image/*" style="padding: 8px 16px;">
                <small style="color: var(--text-muted)">Abaikan jika tidak ingin mengganti file nota saat ini. Maks. 2MB</small>
            </div>

            <!-- Submit buttons -->
            <div style="display: flex; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                <button type="submit" class="btn btn-accent" style="flex: 1;">
                    <i data-lucide="check"></i>
                    <span>Simpan Perubahan</span>
                </button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary" style="flex: 1;">Batal</a>
            </div>
        </form>
    </div>
@endsection
