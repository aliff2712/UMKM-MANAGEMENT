@extends('layouts.app')

@section('title', 'Kasir POS')
@section('page_title', 'Point of Sale (POS) Kasir')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pos.css') }}">
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

{{-- ======== QRIS PAYMENT MODAL ======== --}}
<div id="qris-modal" class="qris-modal-overlay" style="display:none;" onclick="closeQrisModal(event)">
    <div class="qris-modal-card" id="qris-modal-card">

        <div class="qris-modal-header">
            <div class="qris-header-left">
                <span class="qris-badge-pill">QRIS</span>
                <span class="qris-header-sub">Pembayaran Digital</span>
            </div>
            <button type="button" class="qris-close-btn" onclick="cancelQrisPayment()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="qris-modal-body">
            <div class="qris-amount-card">
                <div>
                    <div class="qris-amount-label">Total Pembayaran</div>
                    <div class="qris-amount-value" id="qris-total-display">Rp 0</div>
                </div>
                <div class="qris-waiting-badge">
                    <span class="qris-dot-pulse"></span>
                    <span>Menunggu</span>
                </div>
            </div>

            <div class="qris-qr-frame">
                <span class="qris-corner qris-c-tl"></span>
                <span class="qris-corner qris-c-tr"></span>
                <span class="qris-corner qris-c-bl"></span>
                <span class="qris-corner qris-c-br"></span>
                <img src="{{ asset('images/QRIS.jpeg') }}" alt="QRIS QR Code" style="width:180px; height:180px; display:block; border-radius:8px;">
            </div>

            <p class="qris-scan-hint">Arahkan kamera ke QR Code di atas<br>menggunakan aplikasi e-wallet atau m-banking</p>

            <div class="qris-steps-grid">
                <div class="qris-step-item">
                    <span class="qris-step-num">1</span>
                    <p class="qris-step-text">Buka aplikasi e-wallet / m-banking</p>
                </div>
                <div class="qris-step-item">
                    <span class="qris-step-num">2</span>
                    <p class="qris-step-text">Pilih fitur Scan QR / QRIS</p>
                </div>
                <div class="qris-step-item">
                    <span class="qris-step-num">3</span>
                    <p class="qris-step-text">Arahkan kamera ke QR Code</p>
                </div>
                <div class="qris-step-item">
                    <span class="qris-step-num">4</span>
                    <p class="qris-step-text">Konfirmasi nominal pembayaran</p>
                </div>
            </div>
        </div>

        <div class="qris-modal-footer">
            <button type="button" class="btn-qris-cancel" onclick="cancelQrisPayment()">Batal</button>
            <button type="button" class="btn-qris-confirm" id="qris-confirm-btn" onclick="confirmQrisPayment()">
                <i data-lucide="check-circle-2"></i>
                <span>Pembayaran Selesai</span>
            </button>
        </div>
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
        } else if (method === 'qris') {
            paidLabel.textContent = 'Nominal Pembayaran QRIS';
            paidInput.value = grandTotal;
            paidInput.readOnly = true;
            changeBox.style.display = 'none';
            openQrisModal();
        } else {
            paidLabel.textContent = 'Nominal Pembayaran Non-Tunai';
            paidInput.value = grandTotal;
            paidInput.readOnly = true;
            changeBox.style.display = 'none';
        }
    }

    function openQrisModal() {
        if (cart.length === 0) {
            alert('Keranjang masih kosong! Tambahkan produk terlebih dahulu.');
            document.getElementById('payment_method').value = 'cash';
            handlePaymentMethodChange();
            return;
        }
        const totalText = document.getElementById('summary-total').textContent;
        document.getElementById('qris-total-display').textContent = 'Rp ' + totalText;

        const modal = document.getElementById('qris-modal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('qris-modal-open');
            document.getElementById('qris-modal-card').classList.add('qris-card-open');
        }, 10);
        if (window.lucide) lucide.createIcons();
    }

    function closeQrisModal(event) {
        if (event && event.target !== document.getElementById('qris-modal')) return;
        cancelQrisPayment();
    }

    function cancelQrisPayment() {
        const modal = document.getElementById('qris-modal');
        const card = document.getElementById('qris-modal-card');
        modal.classList.remove('qris-modal-open');
        card.classList.remove('qris-card-open');
        setTimeout(() => { modal.style.display = 'none'; }, 280);

        // Reset ke cash
        document.getElementById('payment_method').value = 'cash';
        handlePaymentMethodChange();
    }

    function confirmQrisPayment() {
        const btn = document.getElementById('qris-confirm-btn');
        btn.innerHTML = '<i data-lucide="loader-circle" style="animation: spin 1s linear infinite;"></i> <span>Memproses...</span>';
        btn.disabled = true;
        if (window.lucide) lucide.createIcons();

        setTimeout(() => {
            const modal = document.getElementById('qris-modal');
            const card = document.getElementById('qris-modal-card');
            modal.classList.remove('qris-modal-open');
            card.classList.remove('qris-card-open');
            setTimeout(() => { modal.style.display = 'none'; }, 280);

            // Submit form
            document.getElementById('checkout-form').submit();
        }, 800);
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

        const method = document.getElementById('payment_method').value;
        if (method === 'qris') {
            // QRIS sudah dikonfirmasi lewat modal, allow submit
            return;
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
@endsection