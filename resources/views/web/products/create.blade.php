@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page_title', 'Tambah Produk Baru')

@section('content')
    <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
        <div class="glass-card-header">
            <h3 class="glass-card-title text-primary">
                <i data-lucide="package-plus"></i>
                <span>Formulir Produk Baru</span>
            </h3>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Batal</a>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="mt-4" enctype="multipart/form-data">>
            @csrf
            
            <!-- Hidden Outlet ID -->
            <input type="hidden" name="outlet_id" value="{{ $outletId }}">

            <div class="form-grid">
                <!-- Left Column -->
                <div>
                    <!-- Nama Produk -->
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Produk *</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Masukan nama produk..." value="{{ old('name') }}" required>
                    </div>

                    <!-- SKU -->
                    <div class="form-group">
                        <label class="form-label" for="sku">Kode SKU *</label>
                        <input type="text" name="sku" id="sku" class="form-control" placeholder="Masukan kode SKU produk..." value="{{ old('sku') }}" required>
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label class="form-label" for="category_id">Kategori Produk</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Satuan -->
                    <div class="form-group">
                        <label class="form-label" for="unit">Satuan Barang *</label>
                        <input type="text" name="unit" id="unit" class="form-control" placeholder="pcs, kg, box, pack, dll." value="{{ old('unit', 'pcs') }}" required>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Harga Beli -->
                    <div class="form-group">
                        <label class="form-label" for="purchase_price">Harga Beli Bruto (Rp) *</label>
                        <input type="number" name="purchase_price" id="purchase_price" class="form-control" placeholder="0" min="0" value="{{ old('purchase_price') }}" required>
                    </div>

                    <!-- Harga Jual -->
                    <div class="form-group">
                        <label class="form-label" for="selling_price">Harga Jual Net (Rp) *</label>
                        <input type="number" name="selling_price" id="selling_price" class="form-control" placeholder="0" min="0" value="{{ old('selling_price') }}" required>
                    </div>

                    <!-- Stok Awal -->
                    <div class="form-group">
                        <label class="form-label" for="stock_qty">Stok Awal (Quantity) *</label>
                        <input type="number" name="stock_qty" id="stock_qty" class="form-control" placeholder="0" min="0" value="{{ old('stock_qty', 0) }}" required>
                    </div>

                    <!-- Batas Stok Minimum -->
                    <div class="form-group">
                        <label class="form-label" for="stock_minimum">Stok Minimum (Alert) *</label>
                        <input type="number" name="stock_minimum" id="stock_minimum" class="form-control" placeholder="5" min="0" value="{{ old('stock_minimum', 5) }}" required>
                    </div>
                </div>
            </div>

            <!-- Is Active -->
            <div class="form-group mb-6">
                <label class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <span>Aktifkan produk ini agar dapat ditransaksikan</span>
                </label>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-lucide="save"></i>
                    <span>Simpan Produk Baru</span>
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary" style="flex: 1;">Batal</a>
            </div>
        </form>
    </div>
@endsection
