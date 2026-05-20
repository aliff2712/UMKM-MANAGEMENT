@extends('layouts.app')

@section('title', 'Koreksi Stok')
@section('page_title', 'Penyesuaian Stok Manual')

@section('content')
    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-warning">
                <i data-lucide="sliders"></i>
                <span>Formulir Koreksi Persediaan</span>
            </h3>
            <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm">Batal</a>
        </div>

        <form action="{{ route('stock.movements.store') }}" method="POST" class="mt-4">
            @csrf

            <!-- Product Selection -->
            <div class="form-group">
                <label class="form-label" for="product_id">Pilih Produk *</label>
                <select name="product_id" id="product_id" class="form-control" required onchange="updateStockMeta()">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                        @php
                            $selectedId = request('product_id');
                        @endphp
                        <option value="{{ $p->id }}" data-stock="{{ $p->stock_qty }}" data-unit="{{ $p->unit }}" {{ old('product_id', $selectedId) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} [{{ $p->sku }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Current Stock Display Metadata -->
            <div id="stock-meta-box" style="display: none; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px; border-radius: var(--border-radius-sm); margin-bottom: 20px;">
                <div class="flex-between">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Stok Tercatat Sekarang:</span>
                    <strong><span id="current-stock-qty">0</span> <span id="current-stock-unit">pcs</span></strong>
                </div>
            </div>

            <!-- Adjustment Type -->
            <div class="form-group">
                <label class="form-label" for="type">Jenis Pergerakan Stok *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stok Masuk (Restock / Barang Datang +)</option>
                    <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stok Keluar (Retur / Rusak / Susut -)</option>
                    <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Koreksi Selisih Opname (Mutlak)</option>
                </select>
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label class="form-label" for="qty">Jumlah (Quantity) *</label>
                <input type="number" name="qty" id="qty" class="form-control" placeholder="Masukan nominal kuantitas..." min="1" value="{{ old('qty') }}" required>
            </div>

            <!-- Note / Reason -->
            <div class="form-group mb-6">
                <label class="form-label" for="note">Catatan / Alasan Penyesuaian</label>
                <textarea name="note" id="note" class="form-control" placeholder="Contoh: 'Pengadaan bahan baku bulanan', 'Barang pecah di gudang', dll.">{{ old('note') }}</textarea>
            </div>

            <!-- Submit buttons -->
            <div style="display: flex; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="check"></i>
                    <span>Terapkan Perubahan</span>
                </button>
                <a href="{{ route('stock.index') }}" class="btn btn-secondary" style="flex: 1;">Batal</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function updateStockMeta() {
            const selectEl = document.getElementById('product_id');
            const metaBox = document.getElementById('stock-meta-box');
            const stockQtyEl = document.getElementById('current-stock-qty');
            const stockUnitEl = document.getElementById('current-stock-unit');
            
            if (selectEl.value === '') {
                metaBox.style.display = 'none';
                return;
            }

            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const stock = selectedOpt.getAttribute('data-stock');
            const unit = selectedOpt.getAttribute('data-unit');
            
            stockQtyEl.textContent = stock;
            stockUnitEl.textContent = unit;
            metaBox.style.display = 'block';
        }

        // Trigger on load if there's old selected value
        document.addEventListener('DOMContentLoaded', function() {
            updateStockMeta();
        });
    </script>
@endsection
