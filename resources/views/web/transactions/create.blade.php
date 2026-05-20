@extends('layouts.app')

@section('title', 'Kasir POS')
@section('page_title', 'Point of Sale (POS) Kasir')

@section('content')
    <div class="pos-layout">
        
        <!-- LEFT COLUMN: Product Catalog -->
        <div class="glass-card" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="flex-between">
                <h3 class="glass-card-title text-cyan">
                    <i data-lucide="shopping-bag"></i>
                    <span>Katalog Produk</span>
                </h3>
                <input type="text" id="product-search" class="form-control" placeholder="Cari nama produk / SKU..." style="max-width: 250px;" oninput="filterPOSProducts()">
            </div>

            <!-- Products catalog grid -->
            <div class="pos-products-grid" id="pos-products-list">
                @foreach($products as $p)
                    @php
                        $isOut = $p->stock_qty <= 0;
                    @endphp
                    <div class="glass-card pos-product-card {{ $isOut ? 'out-of-stock' : '' }}" 
                         data-id="{{ $p->id }}" 
                         data-name="{{ $p->name }}" 
                         data-price="{{ $p->selling_price }}" 
                         data-sku="{{ $p->sku }}" 
                         data-stock="{{ $p->stock_qty }}"
                         onclick="addToCart({{ $p->id }}, {{ $isOut ? 'true' : 'false' }})">
                        
                        <div class="badge badge-info" style="position: absolute; top: 8px; right: 8px; font-size: 0.65rem;">
                            {{ $p->category->name ?? 'Lainnya' }}
                        </div>
                        
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px; text-align: left;">
                            <code>{{ $p->sku }}</code>
                        </div>
                        
                        <h4 style="font-size: 0.95rem; font-weight: 700; text-align: left; margin-bottom: 8px; min-height: 38px;">
                            {{ $p->name }}
                        </h4>
                        
                        <div class="pos-product-price">
                            Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                        </div>

                        <div class="flex-between" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">
                            <span>Sisa Stok:</span>
                            <span class="{{ $isOut ? 'text-error' : ($p->stock_qty < 5 ? 'text-warning' : '') }}">
                                <strong>{{ $p->stock_qty }}</strong> {{ $p->unit ?? 'pcs' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT COLUMN: Shopping Cart Panel -->
        <div class="glass-card pos-cart-panel" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="glass-card-header" style="margin-bottom: 8px;">
                    <h3 class="glass-card-title text-accent">
                        <i data-lucide="shopping-cart"></i>
                        <span>Keranjang Belanja</span>
                    </h3>
                    <button class="btn btn-secondary btn-sm text-error" onclick="clearCart()">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        <span>Kosongkan</span>
                    </button>
                </div>

                <!-- Empty State -->
                <div id="cart-empty-state" style="text-align: center; padding: 48px 16px; color: var(--text-muted);">
                    <i data-lucide="shopping-cart" style="width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p>Keranjang kosong. Klik produk di katalog sebelah kiri untuk berbelanja.</p>
                </div>

                <!-- Cart List -->
                <div class="cart-items-list" id="cart-items-container">
                    <!-- Javascript generated items go here -->
                </div>
            </div>

            <!-- Checkout form -->
            <form action="{{ route('transactions.store') }}" method="POST" id="checkout-form">
                @csrf
                <!-- Hidden Outlet ID -->
                <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id }}">
                
                <!-- Container for dynamically appended hidden inputs of product items -->
                <div id="hidden-items-inputs"></div>

                <!-- Summary calculations -->
                <div style="border-top: 1px solid var(--glass-border); padding-top: 16px; margin-top: 16px;">
                    <div class="pos-summary-row">
                        <span style="color: var(--text-muted);">Subtotal:</span>
                        <strong>Rp <span id="summary-subtotal">0</span></strong>
                    </div>

                    <div class="pos-summary-row" style="align-items: center; margin: 10px 0;">
                        <span style="color: var(--text-muted);">Diskon (Rp):</span>
                        <input type="number" name="discount_amount" id="discount-input" class="form-control" value="0" min="0" style="max-width: 120px; padding: 6px 12px; font-size: 0.85rem; text-align: right;" oninput="calculateTotal()">
                    </div>

                    <div class="pos-total-row">
                        <span>Grand Total:</span>
                        <span>Rp <span id="summary-total">0</span></span>
                    </div>
                </div>

                <!-- Payment specs -->
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 12px; border-radius: var(--border-radius-md); margin: 16px 0;">
                    <!-- Payment method dropdown -->
                    <div class="form-group">
                        <label class="form-label" for="payment_method">Metode Pembayaran *</label>
                        <select name="payment_method" id="payment_method" class="form-control" required onchange="handlePaymentMethodChange()">
                            <option value="cash">Uang Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS (QR Code)</option>
                        </select>
                    </div>

                    <!-- Paid Amount Input -->
                    <div class="form-group">
                        <label class="form-label" for="paid_amount" id="paid-label">Jumlah Uang Diterima *</label>
                        <input type="number" name="paid_amount" id="paid_amount" class="form-control" placeholder="0" min="0" value="0" required oninput="calculateChange()">
                    </div>

                    <!-- Change Display -->
                    <div class="flex-between" id="change-box" style="font-size: 0.9rem; padding: 4px 0;">
                        <span style="color: var(--text-muted);">Kembalian:</span>
                        <strong class="text-success">Rp <span id="summary-change">0</span></strong>
                    </div>
                </div>

                <!-- Note / Customer -->
                <div class="form-group">
                    <label class="form-label" for="note">Catatan Transaksi (Optional)</label>
                    <input type="text" name="note" id="note" class="form-control" placeholder="Nama pelanggan / meja / no. order...">
                </div>

                <!-- Checkout submission button -->
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                    <i data-lucide="check-circle-2"></i>
                    <span>Selesaikan Pembayaran & Cetak</span>
                </button>
            </form>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // In-memory Shopping Cart State
        let cart = [];

        // 1. Add clicked product to shopping cart
        function addToCart(productId, isOutOfStock) {
            if (isOutOfStock) {
                alert('Maaf, produk ini kehabisan stok dan tidak dapat dimasukkan ke keranjang.');
                return;
            }

            const productCard = document.querySelector(`.pos-product-card[data-id="${productId}"]`);
            const name = productCard.getAttribute('data-name');
            const price = parseFloat(productCard.getAttribute('data-price'));
            const sku = productCard.getAttribute('data-sku');
            const stock = parseInt(productCard.getAttribute('data-stock'));

            // Check if product exists in cart
            const existingItem = cart.find(item => item.id === productId);

            if (existingItem) {
                if (existingItem.qty >= stock) {
                    alert(`Gagal menambah kuantitas! Jumlah stok terdaftar maksimal ${stock} ${existingItem.unit || 'pcs'}.`);
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

        // 2. Render cart items to UI
        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const emptyState = document.getElementById('cart-empty-state');
            
            if (cart.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'block';
                document.getElementById('summary-subtotal').textContent = '0';
                document.getElementById('summary-total').textContent = '0';
                document.getElementById('summary-change').textContent = '0';
                document.getElementById('paid_amount').value = 0;
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

                // Build hidden inputs for backend submission
                hiddenInputsHtml += `
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                `;

                // Build cart item row UI
                const itemEl = document.createElement('div');
                itemEl.className = 'cart-item';
                itemEl.innerHTML = `
                    <div style="flex: 1; min-width: 0;">
                        <h5 style="font-size: 0.85rem; font-weight: 700; margin-bottom: 2px;" class="text-truncate">${item.name}</h5>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">
                            Rp ${numberFormat(item.price)}
                        </small>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px; margin: 0 12px;">
                        <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 6px; font-size: 0.75rem;" onclick="decrementQty(${item.id})">-</button>
                        <strong style="font-size: 0.85rem;">${item.qty}</strong>
                        <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 6px; font-size: 0.75rem;" onclick="incrementQty(${item.id})">+</button>
                    </div>
                    
                    <div style="text-align: right; min-width: 80px;">
                        <strong style="font-size: 0.85rem; color: var(--cyan);">Rp ${numberFormat(itemTotal)}</strong>
                    </div>
                    
                    <button type="button" style="background: none; border: none; color: var(--error); cursor: pointer; padding: 4px; margin-left: 8px;" onclick="removeFromCart(${item.id})">
                        <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                    </button>
                `;
                container.appendChild(itemEl);
            });

            document.getElementById('hidden-items-inputs').innerHTML = hiddenInputsHtml;
            lucide.createIcons();
            
            document.getElementById('summary-subtotal').textContent = numberFormat(subtotal);
            
            calculateTotal(subtotal);
        }

        // 3. Increment/Decrement/Remove Cart handlers
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

        // 4. Calculate Subtotal, Discount & Total
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

            // Default paid value if other methods
            const method = document.getElementById('payment_method').value;
            if (method !== 'cash') {
                document.getElementById('paid_amount').value = grandTotal;
            }

            calculateChange(grandTotal);
        }

        // 5. Calculate cash change
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

        // 6. Handle Payment Method Selection
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
                // If transfer or QRIS, paid matches grand total exactly
                paidLabel.textContent = 'Nominal Pembayaran Non-Tunai';
                paidInput.value = grandTotal;
                paidInput.readOnly = true;
                changeBox.style.display = 'none';
            }
        }

        // 7. Search & filter POS catalog items
        function filterPOSProducts() {
            const searchVal = document.getElementById('product-search').value.toLowerCase();
            const cards = document.querySelectorAll('.pos-product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const sku = card.getAttribute('data-sku').toLowerCase();
                
                if (name.includes(searchVal) || sku.includes(searchVal)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Form submit validator
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                alert('Error: Keranjang belanja kosong! Silakan tambahkan minimal 1 produk.');
                e.preventDefault();
                return;
            }

            const paidInput = document.getElementById('paid_amount');
            const paid = parseFloat(paidInput.value) || 0;
            const totalText = document.getElementById('summary-total').textContent;
            const grandTotal = parseFloat(totalText.replace(/\./g, '')) || 0;

            if (paid < grandTotal) {
                alert(`Error: Uang diterima (Rp ${numberFormat(paid)}) kurang dari total tagihan (Rp ${numberFormat(grandTotal)})!`);
                e.preventDefault();
                return;
            }
        });

        // Numeric formatter utility
        function numberFormat(val) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(val);
        }
    </script>
    
    <style>
        .out-of-stock {
            opacity: 0.55;
            background: rgba(244, 63, 94, 0.05) !important;
            border-color: rgba(244, 63, 94, 0.15) !important;
            cursor: not-allowed !important;
        }
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endsection
