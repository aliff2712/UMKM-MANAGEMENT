@extends('layouts.app')

@section('title', 'Kasir POS')
@section('page_title', 'Point of Sale (POS) Kasir')

@section('content')
<div class="pos-container">

    <div class="pos-catalog-section">
        <div class="flex-between" style="align-items: center; margin-bottom: 4px;">
            <h3 class="pos-section-title">
                <i data-lucide="shopping-bag"></i>
                <span>Katalog Produk</span>
            </h3>
            <div class="pos-search-wrapper">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="product-search" class="pos-search-input" placeholder="Cari nama produk / SKU..." oninput="filterPOSProducts()">
            </div>
        </div>

        <div class="pos-category-scroll">
            <button class="category-btn active" onclick="filterCategory('semua', this)">Semua</button>
            @if(isset($categories) && $categories->count() > 0)
            @foreach($categories as $cat)
            <button class="category-btn" onclick="filterCategory('{{ strtolower($cat->name) }}', this)">
                {{ $cat->name }}
            </button>
            @endforeach
            @else
            @foreach($products->pluck('category.name')->unique()->filter() as $catName)
            <button class="category-btn" onclick="filterCategory('{{ strtolower($catName) }}', this)">
                {{ $catName }}
            </button>
            @endforeach
            @endif
        </div>

        <div class="pos-products-grid" id="pos-products-list">
            @foreach($products as $p)
            @php
                $isOut = $p->stock_qty <= 0;
                $currentCategory = strtolower($p->category->name ?? 'lainnya');
            @endphp
                <div class="product-card {{ $isOut ? 'out-of-stock' : '' }}"
                    data-id="{{ $p->id }}"
                    data-name="{{ $p->name }}"
                    data-price="{{ $p->selling_price }}"
                    data-sku="{{ $p->sku }}"
                    data-stock="{{ $p->stock_qty }}"
                    data-category="{{ $currentCategory }}"
                    onclick="addToCart(this)">

                    <div class="badge-category">
                        {{ $p->category->name ?? 'Lainnya' }}
                    </div>

                    <div class="product-image-placeholder">
                        @if(str_contains($currentCategory, 'minuman')) 🍹
                        @elseif(str_contains($currentCategory, 'makanan')) 🍔
                        @elseif(str_contains($currentCategory, 'snack')) 🍿
                        @else 📦 @endif
                    </div>

                    <div class="product-info">
                        <div class="product-sku"><code>{{ $p->sku }}</code></div>
                        <h4 class="product-title">{{ $p->name }}</h4>
                        <div class="product-price">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>

                        <div class="product-stock-status">
                            <span>Sisa Stok:</span>
                            <span class="{{ $isOut ? 'text-error' : ($p->stock_qty < 5 ? 'text-warning' : '') }}">
                                <strong>{{ $p->stock_qty }}</strong> {{ $p->unit ?? 'pcs' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
        </div>
    </div>

    <div class="pos-cart-panel">
        <div class="cart-header">
            <h3 class="cart-title">
                <i data-lucide="shopping-cart"></i>
                <span>Keranjang Belanja</span>
                <span id="cart-item-count" style="font-size: 0.85rem; font-weight: normal; color: #64748b;">(0 items)</span>
            </h3>
            <button class="btn-clear-cart" onclick="clearCart()">
                <i data-lucide="trash-2"></i>
                <span>Kosongkan</span>
            </button>
        </div>

        <div class="cart-items-list" id="cart-items-container">
        </div>

        <div id="cart-empty-state" class="cart-empty-state">
            <i data-lucide="shopping-cart" class="empty-icon"></i>
            <p>Keranjang kosong. Klik produk di katalog sebelah kiri untuk berbelanja.</p>
        </div>

        <form action="{{ route('transactions.store') }}" method="POST" id="checkout-form" class="checkout-footer">
            @csrf
            <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id }}">
            <div id="hidden-items-inputs"></div>

            <div class="summary-box">
                <div class="pos-summary-row">
                    <span>Subtotal:</span>
                    <strong>Rp <span id="summary-subtotal">0</span></strong>
                </div>

                <div class="pos-summary-row align-center">
                    <span>Diskon (Rp):</span>
                    <input type="number" name="discount_amount" id="discount-input" class="form-input-sm" value="0" min="0" style="max-width: 120px; text-align: right;" oninput="calculateTotal()">
                </div>

                <div class="pos-total-row">
                    <span>Grand Total:</span>
                    <span>Rp <span id="summary-total">0</span></span>
                </div>
            </div>

            <div class="payment-details-box">
                <div class="form-group-sm">
                    <label for="payment_method">Metode Pembayaran *</label>
                    <select name="payment_method" id="payment_method" class="form-select-sm" required onchange="handlePaymentMethodChange()">
                        <option value="cash">Uang Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS (QR Code)</option>
                    </select>
                </div>

                <div class="form-group-sm" style="margin-top: 8px;">
                    <label for="paid_amount" id="paid-label">Jumlah Uang Diterima *</label>
                    <input type="number" name="paid_amount" id="paid_amount" class="form-input-sm full-width" placeholder="Masukan nominal tunai..." min="0" required oninput="calculateChange()">
                </div>

                <div class="pos-summary-row align-center" id="change-box" style="margin-top: 8px;">
                    <span>Kembalian:</span>
                    <strong class="text-success">Rp <span id="summary-change">0</span></strong>
                </div>
            </div>

            <div class="form-group-sm" style="margin-bottom: 12px;">
                <label for="note">Catatan Transaksi (Optional)</label>
                <input type="text" name="note" id="note" class="form-input-sm full-width" placeholder="Nama pelanggan / meja / no. order...">
            </div>

            <button type="submit" class="btn-checkout">
                <i data-lucide="check-circle-2"></i>
                <span>Selesaikan Pembayaran & Cetak</span>
            </button>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    let cart = [];

    // Fungsi addToCart disesuaikan menerima elemen HTML (this)
    function addToCart(element) {
        const stock = parseInt(element.getAttribute('data-stock'));
        const isOutOfStock = stock <= 0;

        if (isOutOfStock) {
            alert('Maaf, produk ini kehabisan stok dan tidak dapat dimasukkan ke keranjang.');
            return;
        }

        const productId = parseInt(element.getAttribute('data-id'));
        const name = element.getAttribute('data-name');
        const price = parseFloat(element.getAttribute('data-price'));
        const sku = element.getAttribute('data-sku');

        const existingItem = cart.find(item => item.id === productId);

        if (existingItem) {
            if (existingItem.qty >= stock) {
                alert(`Gagal menambah kuantitas! Jumlah stok terdaftar maksimal ${stock} pcs.`);
                return;
            }
            existingItem.qty += 1;
        } else {
            cart.push({
                id: productId,
                sku: sku,
                name: name,
                price: price,
                qty: 1,
                stock: stock
            });
        }

        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty-state');
        const itemCountElement = document.getElementById('cart-item-count');

        if (itemCountElement) {
            itemCountElement.textContent = `(${cart.length} item${cart.length > 1 ? 's' : ''})`;
        }

        if (cart.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'flex';
            document.getElementById('summary-subtotal').textContent = '0';
            document.getElementById('summary-total').textContent = '0';
            document.getElementById('summary-change').textContent = '0';
            document.getElementById('paid_amount').value = '';
            document.getElementById('hidden-items-inputs').innerHTML = '';
            return;
        }

        emptyState.style.display = 'none';
        container.innerHTML = '';
        let subtotal = 0;
        let hiddenInputsHtml = '';

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;

            hiddenInputsHtml += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
            `;

            const itemEl = document.createElement('div');
            itemEl.className = 'cart-item';
            itemEl.innerHTML = `
                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center;">
                    <h5 style="font-size: 0.85rem; font-weight: 700; margin: 0; line-height: 1.2; color:#0f172a;" class="text-truncate">${item.name}</h5>
                    <small style="color: #64748b; font-size: 0.75rem; margin-top: 2px; line-height: 1.1;">
                        Rp ${numberFormat(item.price)}
                    </small>
                </div>
                
                <div style="display: flex; align-items: center; gap: 4px; margin: 0 12px; height: 28px;">
                    <button type="button" class="btn-qty" onclick="decrementQty(${item.id})">-</button>
                    <strong style="font-size: 0.85rem; width: 24px; text-align:center; display: inline-block; line-height: 28px;">${item.qty}</strong>
                    <button type="button" class="btn-qty" onclick="incrementQty(${item.id})">+</button>
                </div>
                
                <div style="text-align: right; min-width: 80px; display: flex; align-items: center; justify-content: flex-end;">
                    <strong style="font-size: 0.85rem; color: #2563eb;">Rp ${numberFormat(itemTotal)}</strong>
                </div>
                
                <div style="display: flex; align-items: center; justify-content: center; margin-left: 8px;">
                    <button type="button" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;" onclick="removeFromCart(${item.id})">
                        <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
            `;
            container.appendChild(itemEl);
        });

        document.getElementById('hidden-items-inputs').innerHTML = hiddenInputsHtml;
        if (window.lucide) {
            lucide.createIcons();
        }

        document.getElementById('summary-subtotal').textContent = numberFormat(subtotal);
        calculateTotal(subtotal);
    }

    function incrementQty(productId) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            if (item.qty >= item.stock) {
                alert(`Gagal menambah kuantitas! Jumlah stok terdaftar maksimal ${item.stock}.`);
                return;
            }
            item.qty += 1;
            renderCart();
        }
    }

    function decrementQty(productId) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.qty -= 1;
            if (item.qty <= 0) {
                removeFromCart(productId);
            } else {
                renderCart();
            }
        }
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        renderCart();
    }

    function clearCart() {
        if (confirm('Kosongkan keranjang belanja?')) {
            cart = [];
            renderCart();
        }
    }

    function calculateTotal(subtotalVal) {
        let subtotal = subtotalVal;
        if (subtotal === undefined) {
            subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        const discountInput = document.getElementById('discount-input');
        let discount = parseFloat(discountInput.value) || 0;

        if (discount < 0) {
            discount = 0;
            discountInput.value = 0;
        }

        const grandTotal = Math.max(0, subtotal - discount);
        document.getElementById('summary-total').textContent = numberFormat(grandTotal);

        const method = document.getElementById('payment_method').value;
        if (method !== 'cash') {
            document.getElementById('paid_amount').value = grandTotal;
        }

        calculateChange(grandTotal);
    }

    function calculateChange(grandTotalVal) {
        let grandTotal = grandTotalVal;
        if (grandTotal === undefined) {
            const totalText = document.getElementById('summary-total').textContent;
            grandTotal = parseFloat(totalText.replace(/\./g, '')) || 0;
        }

        const paidInput = document.getElementById('paid_amount');
        let paid = parseFloat(paidInput.value) || 0;

        if (paid < 0) {
            paid = 0;
            paidInput.value = 0;
        }

        const change = Math.max(0, paid - grandTotal);
        document.getElementById('summary-change').textContent = numberFormat(change);
    }

    function handlePaymentMethodChange() {
        const method = document.getElementById('payment_method').value;
        const paidInput = document.getElementById('paid_amount');
        const paidLabel = document.getElementById('paid-label');
        const changeBox = document.getElementById('change-box');

        const totalText = document.getElementById('summary-total').textContent;
        const grandTotal = parseFloat(totalText.replace(/\./g, '')) || 0;

        if (method === 'cash') {
            paidLabel.textContent = 'Jumlah Uang Diterima *';
            paidInput.readOnly = false;
            paidInput.value = '';
            paidInput.placeholder = 'Masukan nominal tunai...';
            changeBox.style.display = 'flex';
            document.getElementById('summary-change').textContent = '0';
        } else {
            paidLabel.textContent = 'Nominal Pembayaran Non-Tunai';
            paidInput.value = grandTotal;
            paidInput.readOnly = true;
            changeBox.style.display = 'none';
        }
    }

    function filterPOSProducts() {
        const searchVal = document.getElementById('product-search').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const sku = card.getAttribute('data-sku').toLowerCase();

            if (name.includes(searchVal) || sku.includes(searchVal)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterCategory(categoryName, element) {
        const buttons = document.querySelectorAll('.category-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');

        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const productCat = card.getAttribute('data-category');
            if (categoryName === 'semua' || productCat === categoryName) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            alert('Error: Keranjang belanja kosong! Silakan tambahkan minimal 1 produk.');
            return e.preventDefault();
        }

        const paidInput = document.getElementById('paid_amount');
        const paid = parseFloat(paidInput.value) || 0;
        const totalText = document.getElementById('summary-total').textContent;
        const grandTotal = parseFloat(totalText.replace(/\./g, '')) || 0;

        if (paid < grandTotal) {
            alert(`Error: Uang diterima (Rp ${numberFormat(paid)}) kurang dari total tagihan (Rp ${numberFormat(grandTotal)})!`);
            return e.preventDefault();
        }
    });

    function numberFormat(val) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        }).format(val);
    }
</script>

<style>
    /* ─── FIGMA BRIGHT CLEAN MINIMALIST RESETS ─── */
    body {
        background-color: #f8fafc !important;
    }

    .pos-container {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 0;
        height: calc(100vh - 120px);
        margin: -20px;
    }

    /* ─── LEFT SIDE CONTENT ─── */
    .pos-catalog-section {
        background: #ffffff;
        padding: 24px;
        border-right: 2px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow-y: auto;
    }

    .pos-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .pos-section-title i {
        width: 20px;
        height: 20px;
        color: #2563eb;
    }

    .pos-search-wrapper {
        position: relative;
        width: 280px;
    }

    .pos-search-wrapper .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        width: 16px;
        height: 16px;
    }

    .pos-search-input {
        width: 100%;
        padding: 8px 12px 8px 38px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 20px;
        font-size: 0.85rem;
        color: #1e293b;
        outline: none;
    }

    .pos-search-input:focus {
        border-color: #2563eb;
    }

    .pos-category-scroll {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        border-bottom: 1px solid #f1f5f9;
    }

    .pos-category-scroll::-webkit-scrollbar {
        height: 4px;
    }

    .pos-category-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .category-btn {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #475569;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .category-btn.active,
    .category-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .pos-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 16px;
        padding-top: 4px;
    }

    .product-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: transform 0.15s, border-color 0.15s;
    }

    .product-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    }

    .badge-category {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .product-image-placeholder {
        background: #f1f5f9;
        aspect-ratio: 4/3;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-top: 12px;
    }

    .product-sku {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .product-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
        margin: 2px 0 4px 0;
        line-height: 1.3;
        min-height: 34px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2563eb;
    }

    .product-stock-status {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #64748b;
        border-top: 1px dashed #f1f5f9;
        padding-top: 6px;
        margin-top: 2px;
    }

    /* ─── RIGHT SIDE CART ─── */
    .pos-cart-panel {
        background: #ffffff;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .cart-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cart-title i {
        color: #2563eb;
        width: 18px;
        height: 18px;
    }

    .btn-clear-cart {
        background: none;
        border: none;
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .btn-clear-cart i {
        width: 14px;
        height: 14px;
    }

    .cart-empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        color: #94a3b8;
        text-align: center;
    }

    .cart-empty-state .empty-icon {
        width: 44px;
        height: 44px;
        margin-bottom: 8px;
        opacity: 0.4;
    }

    .cart-empty-state p {
        font-size: 0.8rem;
        margin: 0;
    }

    .cart-items-list {
        flex: 1;
        overflow-y: auto;
        padding: 0;
    }

    .cart-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px !important;
        border-bottom: 1px solid #f1f5f9;
        min-height: 56px;
        box-sizing: border-box;
        width: 100% !important;
    }

    .btn-qty {
        background: #f1f5f9;
        border: none;
        color: #475569;
        width: 24px;
        height: 28px;
        font-size: 0.85rem;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .btn-qty:hover {
        background: #e2e8f0;
    }

    .checkout-footer {
        border-top: 1px solid #e2e8f0;
        padding: 16px 20px;
        background: #f8fafc;
    }

    .summary-box {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
    }

    .pos-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #475569;
    }

    .pos-summary-row.align-center {
        align-items: center;
    }

    .pos-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 1.15rem;
        font-weight: 800;
        color: #2563eb;
        border-top: 1px dashed #cbd5e1;
        padding-top: 6px;
        margin-top: 2px;
    }

    .payment-details-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group-sm {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .form-group-sm label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }

    .form-input-sm,
    .form-select-sm {
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #0f172a;
        outline: none;
    }

    .form-input-sm.full-width {
        width: 100%;
    }

    .btn-checkout {
        background: #2563eb;
        color: #ffffff;
        border: none;
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-checkout:hover {
        background: #1d4ed8;
    }

    .out-of-stock {
        opacity: 0.5;
        background: #f8fafc;
        cursor: not-allowed !important;
    }

    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .text-error {
        color: #ef4444;
    }

    .text-warning {
        color: #f59e0b;
    }

    .text-success {
        color: #10b981;
    }
</style>
@endsection