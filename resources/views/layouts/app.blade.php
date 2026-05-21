<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - TechneFest UMKM</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Core Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    @yield('styles')
</head>
<body>

    <!-- Ambient glowing orbs for premium visual -->
    <div class="ambient-orb orb-top-right"></div>
    <div class="ambient-orb orb-bottom-left"></div>

    <div class="app-container">
        
        <!-- ── SIDEBAR ── -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i data-lucide="store"></i>
                </div>
                <span class="brand-text">TechneFest</span>
            </div>
            
            <nav class="sidebar-menu">
                
                <!-- MENU UMUM -->
                <div class="menu-section">
                    <span class="menu-title">Umum</span>
                    <a href="{{ route('dashboard') }}" class="menu-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                @php
                    $role = auth()->user()->role ?? '';
                @endphp

                <!-- HAK AKSES: OWNER ONLY -->
                @if($role === 'owner')
                    <div class="menu-section">
                        <span class="menu-title">Analitik Bisnis</span>
                        <a href="{{ route('insights.index') }}" class="menu-item {{ Request::routeIs('insights.*') ? 'active' : '' }}">
                            <i data-lucide="trending-up"></i>
                            <span>Business Insights</span>
                        </a>
                        <a href="{{ route('reports.profit-loss') }}" class="menu-item {{ Request::routeIs('reports.profit-loss') ? 'active' : '' }}">
                            <i data-lucide="file-spreadsheet"></i>
                            <span>Laba & Rugi</span>
                        </a>
                        <a href="{{ route('chatbot.index') }}" class="menu-item {{ Request::routeIs('chatbot.*') ? 'active' : '' }}">
                            <i data-lucide="message-square-text"></i>
                            <span>Asisten Pintar</span>
                        </a>
                    </div>
                @endif

                <!-- HAK AKSES: OWNER & ADMIN -->
                @if(in_array($role, ['owner', 'admin']))
                    <div class="menu-section">
                        <span class="menu-title">Operasional</span>
                        <a href="{{ route('products.index') }}" class="menu-item {{ Request::routeIs('products.*') ? 'active' : '' }}">
                            <i data-lucide="package"></i>
                            <span>Produk</span>
                        </a>
                        <a href="{{ route('stock.index') }}" class="menu-item {{ Request::routeIs('stock.index') ? 'active' : '' }}">
                            <i data-lucide="boxes"></i>
                            <span>Kelola Stok</span>
                        </a>
                        <a href="{{ route('stock.movements') }}" class="menu-item {{ Request::routeIs('stock.movements') ? 'active' : '' }}">
                            <i data-lucide="arrow-left-right"></i>
                            <span>Riwayat Stok</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="menu-item {{ Request::routeIs('expenses.*') ? 'active' : '' }}">
                            <i data-lucide="wallet"></i>
                            <span>Pengeluaran</span>
                        </a>
                    </div>
                    
                    <div class="menu-section">
                        <span class="menu-title">Laporan</span>
                        <a href="{{ route('reports.sales') }}" class="menu-item {{ Request::routeIs('reports.sales') ? 'active' : '' }}">
                            <i data-lucide="bar-chart-3"></i>
                            <span>Laporan Penjualan</span>
                        </a>
                        <a href="{{ route('reports.expenses') }}" class="menu-item {{ Request::routeIs('reports.expenses') ? 'active' : '' }}">
                            <i data-lucide="pie-chart"></i>
                            <span>Laporan Biaya</span>
                        </a>
                    </div>
                @endif

                <!-- HAK AKSES: OWNER, ADMIN, KASIR -->
                @if(in_array($role, ['owner', 'admin', 'kasir']))
                    <div class="menu-section">
                        <span class="menu-title">Penjualan</span>
                        <a href="{{ route('transactions.create') }}" class="menu-item {{ Request::routeIs('transactions.create') ? 'active' : '' }}">
                            <i data-lucide="shopping-cart"></i>
                            <span>Kasir POS</span>
                        </a>
                        <a href="{{ route('transactions.index') }}" class="menu-item {{ Request::routeIs('transactions.index') || Request::routeIs('transactions.show') ? 'active' : '' }}">
                            <i data-lucide="history"></i>
                            <span>Riwayat Transaksi</span>
                        </a>    
                    </div>
                @endif
                
            </nav>
        </aside>
        
        <!-- ── MAIN WRAPPER ── -->
        <div class="main-wrapper">
            
            <!-- ── HEADER ── -->
            <header class="main-header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button class="mobile-sidebar-toggle" id="sidebar-toggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="header-title-section">
                        <h2>@yield('page_title', 'Ringkasan')</h2>
                    </div>
                </div>
                
                <div class="header-user-section">
                    <div class="user-profile-summary">
                        <div class="user-name">
                            {{ auth()->user()->name ?? 'Pengguna' }}
                            <span class="role-badge role-{{ $role }}">{{ $role }}</span>
                        </div>
                        <div class="user-role">{{ auth()->user()->outlet->name ?? 'Outlet Utama' }}</div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout">
                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </header>
            
            <!-- ── CONTENT BODY ── -->
            <main class="content-body">
                
                <!-- SUCCESS ALERTS -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <!-- ERROR ALERTS -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <div>
                            <ul style="margin-left: 16px; padding-left: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                
                <!-- Main Blade Slot -->
                @yield('content')
                
            </main>
        </div>
    </div>

    <!-- Initialize Lucide Icons & Responsive Mobile Menu Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Init Lucide
            lucide.createIcons();

            // Mobile menu toggle
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function(e) {
                    sidebar.classList.toggle('open');
                    e.stopPropagation();
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                        if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
                            sidebar.classList.remove('open');
                        }
                    }
                });
            }
        });
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>
