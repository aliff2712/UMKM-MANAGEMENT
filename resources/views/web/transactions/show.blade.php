@extends('layouts.app')

@section('title', 'Detail Struk Transaksi')
@section('page_title', 'Struk Bukti Pembayaran')

@section('content')
    <!-- Actions and Print Wrapper -->
    <div class="glass-card mb-6 flex-between hide-on-print">
        <span>Transaksi terdaftar dengan invoice: <strong>{{ $transaction->invoice_number }}</strong>.</span>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i data-lucide="printer"></i>
                <span>Cetak Struk (Print)</span>
            </button>
            <a href="{{ route('transactions.create') }}" class="btn btn-secondary">
                <i data-lucide="shopping-cart"></i>
                <span>POS Kasir Baru</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <!-- Thermal Paper Style Receipt Card -->
    <div class="receipt-paper">
        <div class="receipt-header">
            <div class="receipt-title">{{ strtoupper($transaction->outlet->name ?? 'Toko Utama') }}</div>
            <div style="font-size: 0.75rem;">{{ $transaction->outlet->address ?? 'Jl. Digital Raya No. 101' }}</div>
            <div style="font-size: 0.75rem;">Telp: {{ $transaction->outlet->phone ?? '08123456789' }}</div>
        </div>

        <!-- Metadata info -->
        <div style="font-size: 0.75rem;">
            <div class="receipt-row">
                <span>No. Invoice:</span>
                <strong>{{ $transaction->invoice_number }}</strong>
            </div>
            <div class="receipt-row">
                <span>Tanggal:</span>
                <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="receipt-row">
                <span>Kasir:</span>
                <span>{{ $transaction->user->name ?? 'Kasir' }}</span>
            </div>
            @if($transaction->note)
                <div class="receipt-row">
                    <span>Pelanggan/Catatan:</span>
                    <span>{{ $transaction->note }}</span>
                </div>
            @endif
        </div>

        <div class="receipt-dashed-line"></div>

        <!-- Items list -->
        <div style="margin: 12px 0;">
            @foreach($transaction->items as $item)
                <div class="receipt-item-row">
                    <div style="font-weight: bold;">{{ $item->product->name ?? 'Produk' }}</div>
                    <div class="receipt-item-desc">
                        <span>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="receipt-dashed-line"></div>

        <!-- Calculations -->
        <div style="font-size: 0.8rem;">
            @php
                $subtotal = $transaction->items->reduce(fn($sum, $item) => $sum + ($item->price * $item->qty), 0);
            @endphp
            <div class="receipt-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount_amount > 0)
                <div class="receipt-row">
                    <span>Diskon:</span>
                    <span>-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            
            <div class="receipt-row" style="font-weight: bold; font-size: 0.9rem; margin-top: 4px;">
                <span>GRAND TOTAL:</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            
            <div class="receipt-row" style="margin-top: 8px;">
                <span>Metode Bayar:</span>
                <span>{{ strtoupper($transaction->payment_method) }}</span>
            </div>
            
            <div class="receipt-row">
                <span>Uang Diterima:</span>
                <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->payment_method === 'cash')
                @php
                    $change = $transaction->paid_amount - $transaction->total_amount;
                @endphp
                <div class="receipt-row">
                    <span>Kembalian:</span>
                    <span>Rp {{ number_format(max(0, $change), 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <div class="receipt-dashed-line"></div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 0.75rem; margin-top: 16px;">
            <div>*** TERIMA KASIH ***</div>
            <div style="margin-top: 4px; font-size: 0.65rem;">Powered by TechneFest UMKM</div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Specific print style overrides in the page scope */
        @media print {
            .hide-on-print {
                display: none !important;
            }
            .content-body {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
@endsection
