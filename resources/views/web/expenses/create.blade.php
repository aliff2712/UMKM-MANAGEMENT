@extends('layouts.app')

@section('title', 'Catat Pengeluaran')
@section('page_title', 'Catat Pengeluaran Baru')

@section('content')
    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-primary">
                <i data-lucide="receipt"></i>
                <span>Formulir Pengeluaran Operasional</span>
            </h3>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">Batal</a>
        </div>

        <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            
            <!-- Hidden Outlet ID -->
            <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id }}">

            <!-- Expense Category -->
            <div class="form-group">
                <label class="form-label" for="expense_category_id">Kategori Biaya *</label>
                <select name="expense_category_id" id="expense_category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori Biaya --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Amount -->
            <div class="form-group">
                <label class="form-label" for="amount">Jumlah Biaya / Nominal (Rp) *</label>
                <input type="number" name="amount" id="amount" class="form-control" placeholder="0" min="0" value="{{ old('amount') }}" required>
            </div>

            <!-- Expense Date -->
            <div class="form-group">
                <label class="form-label" for="expense_date">Tanggal Pengeluaran *</label>
                <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">Deskripsi / Detail Biaya</label>
                <textarea name="description" id="description" class="form-control" placeholder="Contoh: 'Beli lampu bohlam baru untuk gudang depan', 'Pembelian kopi sachet 2 pack', dll.">{{ old('description') }}</textarea>
            </div>

            <!-- Receipt Image Attachment -->
            <div class="form-group mb-6">
                <label class="form-label" for="receipt_image">Lampirkan Foto Nota / Kuitansi</label>
                <input type="file" name="receipt_image" id="receipt_image" class="form-control" accept="image/*" style="padding: 8px 16px;">
                <small style="color: var(--text-muted)">File berupa gambar (.jpg, .png, .jpeg), maks. 2MB</small>
            </div>

            <!-- Submit buttons -->
            <div style="display: flex; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="check"></i>
                    <span>Catat Pengeluaran</span>
                </button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary" style="flex: 1;">Batal</a>
            </div>
        </form>
    </div>
@endsection
