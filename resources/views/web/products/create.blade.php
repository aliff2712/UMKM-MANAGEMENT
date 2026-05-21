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

    <form action="{{ route('products.store') }}" method="POST" class="mt-4" enctype="multipart/form-data">
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

        {{-- Gambar Produk --}}
        <div class="form-group mb-6">
            <label class="form-label" for="image">Gambar Produk</label>

            <div id="image-drop-area" style="
        border: 2px dashed var(--glass-border);
        border-radius: var(--border-radius-md);
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        background: #f8fafc;
    " onclick="document.getElementById('image').click()">
                <div id="image-preview-wrap" style="display: none; margin-bottom: 12px;">
                    <img id="image-preview" src="" alt="Preview"
                        style="max-height: 160px; max-width: 100%; border-radius: 8px; object-fit: contain;">
                </div>
                <div id="image-placeholder">
                    <i data-lucide="image-plus" style="width: 36px; height: 36px; color: #94a3b8; margin-bottom: 8px;"></i>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0;">
                        Klik untuk pilih gambar<br>
                        <small style="color: #94a3b8;">JPG, PNG, WEBP — maks. 2MB</small>
                    </p>
                </div>
                <p id="image-filename" style="font-size: 0.78rem; color: #2563eb; margin-top: 8px; display: none;"></p>
            </div>

            <input type="file" name="image" id="image" accept="image/*"
                style="display: none;" onchange="previewImage(this)">

            @error('image')
            <p style="color: var(--error); font-size: 0.8rem; margin-top: 6px;">{{ $message }}</p>
            @enderror
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

@section('scripts')
<script>
    function previewImage(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-preview-wrap').style.display = 'block';
            document.getElementById('image-placeholder').style.display = 'none';
            document.getElementById('image-filename').textContent = file.name;
            document.getElementById('image-filename').style.display = 'block';
        };
        reader.readAsDataURL(file);

        // Highlight border saat ada file
        document.getElementById('image-drop-area').style.borderColor = '#2563eb';
        document.getElementById('image-drop-area').style.background = '#eff6ff';
    }
</script>
@endsection