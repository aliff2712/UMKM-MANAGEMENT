# 🏪 TechneFest — Platform Manajemen UMKM

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Sanctum-3.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
</p>

> Platform manajemen UMKM berbasis Laravel 10 yang memberikan **insight bisnis cerdas** kepada pemilik usaha — mencakup analisis keuangan, stok produk, performa penjualan, laporan berbasis periode, dan AI Chatbot berbasis data real.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Struktur Database](#-struktur-database)
- [Struktur Proyek](#-struktur-proyek)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [API Reference](#-api-reference)
- [Web Routes (Blade)](#-web-routes-blade)
- [Panduan untuk Frontend Developer](#-panduan-untuk-frontend-developer)
- [Kontribusi](#-kontribusi)

---

## ✨ Fitur Utama

| Modul | Deskripsi |
|---|---|
| 📊 **Insight Engine** | Analisis otomatis penjualan, stok, dan keuangan berbasis data real |
| 🤖 **AI Chatbot** | Tanya-jawab bisnis dalam Bahasa Indonesia — dijawab dari database |
| 🧾 **Kasir / POS** | Buat transaksi penjualan atomic dengan validasi stok real-time |
| 📦 **Manajemen Stok** | Pencatatan pergerakan stok dengan audit trail lengkap |
| 💸 **Pengeluaran** | Catat biaya operasional per kategori + upload foto nota |
| 📈 **Laporan** | Laporan penjualan, pengeluaran, dan Profit & Loss per periode |
| 🔐 **Multi-Role** | Role-based access: `owner`, `admin`, `kasir` |
| 🌐 **Dual Interface** | REST API (untuk mobile/Flutter) + Blade Web (untuk browser) |

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                     FRONTEND LAYER                      │
│                                                         │
│   Browser (Blade)          Mobile / External App        │
│   routes/web.php           routes/api.php               │
└──────────────┬──────────────────────┬───────────────────┘
               │                      │
               ▼                      ▼
┌──────────────────────┐  ┌───────────────────────────┐
│  Web Controllers     │  │  Api/V1 Controllers        │
│  (return view())     │  │  (return JsonResponse)     │
│  Web/Dashboard       │  │  Api/V1/Insight            │
│  Web/Insight         │  │  Api/V1/Chatbot            │
│  Web/Product         │  │  Api/V1/Transaction        │
│  Web/Transaction     │  │  Api/V1/Product            │
│  Web/Stock           │  │  Api/V1/StockMovement      │
│  Web/Expense         │  │  Api/V1/Expense            │
│  Web/Report          │  │  Api/V1/Report             │
│  Web/Chatbot         │  └───────────────────────────┘
└──────────────────────┘
               │                      │
               └──────────┬───────────┘
                          ▼
┌─────────────────────────────────────────────────────────┐
│                   SERVICE LAYER                         │
│                                                         │
│  InsightService   ChatbotService   TransactionService   │
│  StockService     ReportService                         │
│                                                         │
│  ← Logic bisnis ada di sini, dipakai kedua interface →  │
└──────────────────────────────┬──────────────────────────┘
                               ▼
┌─────────────────────────────────────────────────────────┐
│                    DATA LAYER                           │
│                                                         │
│  Eloquent Models: Outlet, User, Product, Transaction,   │
│  TransactionItem, StockMovement, Expense, InsightLog,   │
│  Notification, Category, ExpenseCategory                │
└──────────────────────────────┬──────────────────────────┘
                               ▼
                    ┌──────────────────┐
                    │   MySQL Database │
                    └──────────────────┘
```

---

## 🗄️ Struktur Database

```
outlets              → Toko/usaha UMKM
├── users            → Pengguna (owner, admin, kasir)
├── products         → Produk yang dijual
│   ├── categories   → Kategori produk
│   └── stock_movements → Riwayat pergerakan stok
├── transactions     → Transaksi penjualan
│   └── transaction_items → Detail item per transaksi
├── expenses         → Pengeluaran operasional
│   └── expense_categories → Kategori pengeluaran
├── insight_logs     → Log insight otomatis
└── notifications    → Notifikasi sistem
```

### ERD Singkat

| Tabel | Kolom Penting |
|---|---|
| `outlets` | id, name, address, phone |
| `users` | id, outlet_id, name, email, role(owner/admin/kasir), is_active |
| `products` | id, outlet_id, category_id, name, sku, purchase_price, selling_price, stock_qty, stock_minimum |
| `transactions` | id, outlet_id, user_id, invoice_number, total_amount, payment_method(cash/transfer/qris) |
| `transaction_items` | id, transaction_id, product_id, qty, unit_price, purchase_price, subtotal |
| `stock_movements` | id, product_id, user_id, type(in/out/adjustment), qty, note |
| `expenses` | id, outlet_id, user_id, expense_category_id, amount, expense_date, receipt_image |
| `insight_logs` | id, outlet_id, type, title, message, severity(info/warning/critical), metadata(json) |
| `notifications` | id, outlet_id, type, title, message, data(json), is_read, target_role |

---

## 📁 Struktur Proyek

```
app/
├── Models/
│   ├── Outlet.php
│   ├── User.php
│   ├── Category.php
│   ├── Product.php              ← scope: active, byOutlet, lowStock
│   ├── Transaction.php          ← scope: byOutlet, inPeriod, inMonth
│   ├── TransactionItem.php
│   ├── StockMovement.php        ← scope: byProduct, stockIn, stockOut
│   ├── ExpenseCategory.php
│   ├── Expense.php              ← scope: thisMonth, inPeriod, byCategory
│   ├── InsightLog.php           ← cast: metadata → array
│   └── Notification.php         ← method: markAsRead()
│
├── Services/
│   ├── InsightService.php       ← [CORE] Engine analitik utama
│   ├── ChatbotService.php       ← [CORE] Intent matching + handlers
│   ├── TransactionService.php   ← [CORE] Atomic POS transaction
│   ├── StockService.php         ← [CORE] Single entry point stok
│   └── ReportService.php        ← Laporan P&L, penjualan, pengeluaran
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/              ← Return JsonResponse (untuk mobile/API)
│   │   │   ├── InsightController.php
│   │   │   ├── ChatbotController.php
│   │   │   ├── TransactionController.php
│   │   │   ├── ProductController.php
│   │   │   ├── StockMovementController.php
│   │   │   ├── ExpenseController.php
│   │   │   └── ReportController.php
│   │   └── Web/                 ← Return view() + compact() (untuk Blade)
│   │       ├── DashboardController.php
│   │       ├── InsightWebController.php
│   │       ├── ChatbotWebController.php
│   │       ├── TransactionWebController.php
│   │       ├── ProductWebController.php
│   │       ├── StockWebController.php
│   │       ├── ExpenseWebController.php
│   │       └── ReportWebController.php
│   └── Requests/Api/V1/
│       ├── StoreTransactionRequest.php
│       ├── StoreProductRequest.php
│       ├── StoreExpenseRequest.php
│       └── StoreStockMovementRequest.php
│
routes/
├── api.php          ← /api/v1/* dengan auth:sanctum
└── web.php          ← /* dengan middleware auth (session)

resources/views/     ← Blade templates (dibuat oleh tim frontend)
└── web/
    ├── dashboard/
    ├── insights/
    ├── chatbot/
    ├── products/
    ├── transactions/
    ├── stock/
    ├── expenses/
    └── reports/
```

---

## ⚙️ Persyaratan Sistem

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | 8.1+ |
| Laravel | 10.x |
| MySQL / MariaDB | 8.0 / 10.4 |
| Composer | 2.x |
| Node.js (opsional) | 18.x (untuk Vite) |

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/aliff2712/UMKM-MANAGEMENT.git
cd UMKM-MANAGEMENT
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=technefest_umkm
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Migrasi

```bash
php artisan migrate
```

### 6. Setup Storage untuk Upload Foto

```bash
php artisan storage:link
```

### 7. (Opsional) Seed Data Demo

```bash
php artisan db:seed
```

### 8. Jalankan Server

```bash
php artisan serve
```

Aplikasi berjalan di: `http://localhost:8000`

---

## 🔧 Konfigurasi Environment

File `.env` penting:

```env
APP_NAME="TechneFest"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_DATABASE=technefest_umkm

# Sanctum (untuk API token)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Filesystem (untuk upload nota)
FILESYSTEM_DISK=public
```

---

## 📡 API Reference

### Autentikasi

Semua endpoint API (kecuali `/login`) membutuhkan header:
```
Authorization: Bearer {token}
```

#### `POST /api/v1/login`

```json
// Request
{
  "email": "owner@toko.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "Budi", "role": "owner", "outlet_id": 1 },
    "token": "1|abc...",
    "token_type": "Bearer"
  }
}
```

---

### Insight

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/insights?outlet_id=1` | Ringkasan semua insight |
| GET | `/api/v1/insights/sales?outlet_id=1&period=2025-05` | Insight penjualan |
| GET | `/api/v1/insights/stock?outlet_id=1` | Produk stok rendah |
| GET | `/api/v1/insights/financial?outlet_id=1&period=2025-05` | Profit/loss & cashflow |

### Chatbot

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/chatbot/questions` | Template pertanyaan untuk card UI |
| POST | `/api/v1/chatbot/ask` | Kirim pertanyaan |

```json
// POST /api/v1/chatbot/ask
{
  "question": "Produk apa yang sedang mengalami penurunan penjualan?",
  "outlet_id": 1
}

// Response
{
  "success": true,
  "data": {
    "question": "Produk apa yang...",
    "answer": "Terdapat 3 produk yang mengalami penurunan...",
    "data": [...],
    "intent": "penurunan"
  }
}
```

### Transaksi

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/transactions?outlet_id=1` | Riwayat transaksi |
| POST | `/api/v1/transactions` | Buat transaksi baru |
| GET | `/api/v1/transactions/{id}` | Detail transaksi |

```json
// POST /api/v1/transactions
{
  "outlet_id": 1,
  "payment_method": "cash",
  "paid_amount": 50000,
  "discount_amount": 0,
  "items": [
    { "product_id": 3, "qty": 2 },
    { "product_id": 7, "qty": 1 }
  ]
}
```

### Produk

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/products?outlet_id=1&low_stock=true` | Daftar produk (filter: low_stock, category_id, is_active, search) |
| POST | `/api/v1/products` | Tambah produk |
| GET | `/api/v1/products/{id}` | Detail produk |
| PUT | `/api/v1/products/{id}` | Update produk |
| DELETE | `/api/v1/products/{id}` | Nonaktifkan produk |

### Laporan

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/v1/reports/sales?outlet_id=1&start_date=2025-05-01&end_date=2025-05-31` | Laporan penjualan |
| GET | `/api/v1/reports/expenses?outlet_id=1&start_date=...&end_date=...` | Laporan pengeluaran |
| GET | `/api/v1/reports/profit-loss?outlet_id=1&month=05&year=2025` | Laporan laba bersih |

### Format Response Standar

```json
{
  "success": true | false,
  "message": "Pesan deskriptif",
  "data": { ... } | [ ... ] | null
}
```

---

## 🌐 Web Routes (Blade)

Semua route web dilindungi middleware `auth` (session Laravel).

| URL | Controller Method | Deskripsi |
|---|---|---|
| `GET /dashboard` | `DashboardController@index` | Halaman utama |
| `GET /insights` | `InsightWebController@index` | Ringkasan insight |
| `GET /insights/sales` | `InsightWebController@sales` | Insight penjualan |
| `GET /insights/stock` | `InsightWebController@stock` | Insight stok |
| `GET /insights/financial` | `InsightWebController@financial` | Insight keuangan |
| `GET /chatbot` | `ChatbotWebController@index` | Halaman chatbot |
| `POST /chatbot/ask` | `ChatbotWebController@ask` | Kirim pertanyaan |
| `GET /products` | `ProductWebController@index` | Daftar produk |
| `GET /products/create` | `ProductWebController@create` | Form tambah produk |
| `GET /transactions` | `TransactionWebController@index` | Riwayat transaksi |
| `GET /transactions/create` | `TransactionWebController@create` | Halaman kasir/POS |
| `GET /stock` | `StockWebController@index` | Dashboard stok |
| `GET /stock/movements` | `StockWebController@movements` | Riwayat stok |
| `GET /stock/adjust` | `StockWebController@adjust` | Form adjustment stok |
| `GET /expenses` | `ExpenseWebController@index` | Daftar pengeluaran |
| `GET /reports/sales` | `ReportWebController@sales` | Laporan penjualan |
| `GET /reports/expenses` | `ReportWebController@expenses` | Laporan pengeluaran |
| `GET /reports/profit-loss` | `ReportWebController@profitLoss` | Laporan P&L |

---

## 🖥️ Panduan untuk Frontend Developer

### Membuat Blade View

Setiap Web Controller sudah menyiapkan variable via `compact()`. Contoh:

```php
// InsightWebController@financial — mengirim variable ini ke Blade:
compact('period', 'financialInsight', 'totalRevenue', 'totalExpenses', 'netProfit', 'profitMargin', 'status')
```

Gunakan langsung di Blade:

```blade
{{-- resources/views/web/insights/financial.blade.php --}}

<h2>Laporan Keuangan {{ $period }}</h2>

<div class="card {{ $status === 'profit' ? 'card-success' : 'card-danger' }}">
    <p>Total Pendapatan: Rp {{ number_format($totalRevenue) }}</p>
    <p>Total Pengeluaran: Rp {{ number_format($totalExpenses) }}</p>
    <p>Laba Bersih: Rp {{ number_format($netProfit) }}</p>
    <p>Margin: {{ $profitMargin }}%</p>
</div>
```

### Flash Message

Controller sudah mengirim flash message saat redirect. Tambahkan ini di layout:

```blade
{{-- resources/views/layouts/app.blade.php --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
```

### CSRF pada Form

Wajib ada `@csrf` di setiap `<form>` method POST/PUT/DELETE:

```blade
<form action="{{ route('transactions.store') }}" method="POST">
    @csrf
    {{-- field form --}}
    <button type="submit">Proses Transaksi</button>
</form>
```

### Named Routes

Gunakan `route()` helper — tidak perlu hardcode URL:

```blade
<a href="{{ route('products.index') }}">Produk</a>
<a href="{{ route('products.edit', $product->id) }}">Edit</a>
<a href="{{ route('reports.profit-loss') }}">Laporan P&L</a>
```

### Paginasi

Controller yang mengembalikan `paginate()` sudah siap digunakan:

```blade
{{ $products->links() }}           {{-- Blade default --}}
{{ $transactions->withQueryString()->links() }}  {{-- Pertahankan filter --}}
```

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch baru: `git checkout -b feature/nama-fitur`
3. Commit perubahan: `git commit -m "feat: deskripsi fitur"`
4. Push ke branch: `git push origin feature/nama-fitur`
5. Buat Pull Request

### Konvensi Commit

```
feat:     Fitur baru
fix:      Perbaikan bug
refactor: Refactoring kode
docs:     Update dokumentasi
style:    Perubahan format (bukan logic)
```

---

## 📄 Lisensi

Proyek ini menggunakan lisensi [MIT](LICENSE).

---

<p align="center">
  Dibuat dengan ❤️ untuk UMKM Indonesia — TechneFest 2025
</p>
