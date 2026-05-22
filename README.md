# 🏪 Umora — Modern UMKM Management & AI Intelligence Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Sanctum-3.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind--like_CSS-Premium-38BDF8?style=for-the-badge&logo=css3&logoColor=white" />
</p>

> **Umora** adalah platform manajemen Point of Sales (POS), inventaris multi-outlet, dan analisis laporan keuangan otomatis terintegrasi yang dirancang khusus untuk mendorong digitalisasi UMKM (*Usaha Mikro, Kecil, dan Menengah*) di Indonesia agar naik kelas menuju level *enterprise*. 

Dengan memadukan kemudahan operasional kasir harian (*Point of Sales*) dan kecerdasan analisis bisnis di latar belakang (*AI Business Intelligence*), Umora membantu pemilik usaha memantau bisnis mereka secara real-time dan mengambil keputusan strategis berbasis data.

---

## 📋 Daftar Isi

* [1. Deskripsi Proyek](#-1-deskripsi-proyek)
* [2. Dokumentasi Fitur Unggulan](#-2-dokumentasi-fitur-unggulan)
* [3. Arsitektur Sistem & Skema Data](#-3-arsitektur-sistem--skema-data)
* [4. Panduan Instalasi & Penggunaan](#-4-panduan-instalasi--penggunaan)
* [5. Panduan Kontribusi](#-5-panduan-kontribusi)
* [6. API Reference (Dual Interface)](#-6-api-reference-dual-interface)

---

## 📝 1. Deskripsi Proyek

Banyak pelaku UMKM mengalami kegagalan operasional bukan karena produk mereka tidak laku, melainkan karena **kebutaan terhadap data bisnis sendiri**. Beberapa masalah klasik tersebut di antaranya:
* Kehilangan potensi omzet akibat penanganan stok kritis yang terlambat terdeteksi.
* Pencatatan arus kas yang bercampur antara keuangan pribadi dengan operasional bisnis.
* Ketidaktahuan margin laba bersih yang sesungguhnya karena kalkulasi HPP (*Harga Pokok Penjualan*) yang tidak akurat.

**Umora** hadir sebagai solusi komprehensif untuk mendigitalisasi operasional dari hulu ke hilir. Platform ini menggunakan arsitektur berlapis (*layered architecture*) di Laravel 10 dengan pemisahan tegas antara presentasi (Blade Views untuk desktop dan REST API untuk aplikasi mobile/klien eksternal) dengan logika bisnis utama pada *Service Layer*.

---

## 🚀 2. Dokumentasi Fitur Unggulan

Platform Umora dilengkapi dengan 5 pilar fitur premium yang siap memberikan pengalaman operasional terbaik:

### 📱 A. Point of Sales (POS) & Kasir Modern dengan QRIS Dinamis
* **Deskripsi:** Antarmuka kasir yang responsif, cepat, dan ringan. Dilengkapi validasi stok dinamis agar menghindari transaksi barang yang kosong.
* **Keunggulan:** Mendukung pembayaran non-tunai dengan **Dynamic QRIS Popup Modal**. Saat metode pembayaran QRIS dipilih, sistem memicu popup kode QR interaktif sehingga pelanggan dapat langsung memindai tanpa risiko kesalahan input manual nominal belanja.

### 📦 B. Manajemen Inventaris Multi-Outlet & Peringatan Stok Kritis
* **Deskripsi:** Pemilik usaha dapat memantau beberapa outlet sekaligus. Setiap pergerakan stok (masuk, keluar, *adjustment*) tercatat secara rinci dalam *audit trail* terenkripsi.
* **Keunggulan:** Dilengkapi sistem filter cerdas yang membagi stok ke dalam kategori **Kritis** (stok habis) dan **Warning** (mendekati batas minimum), meminimalkan risiko kehabisan persediaan barang terlaris.

### 🤖 C. Asisten AI Bisnis Pintar (Umora AI Chatbot)
* **Deskripsi:** Chatbot analitik bisnis terintegrasi yang didukung oleh API Gemini dengan sistem *fallback chain* otomatis (`gemini-2.5-flash-lite` & `gemini-2.5-flash`).
* **Keunggulan:** Pemilik bisnis (*owner*) dapat berkonsultasi mengenai performa toko menggunakan bahasa Indonesia yang santun. Chatbot secara cerdas menganalisis basis data internal outlet secara real-time dan memberikan tips optimasi operasional serta finansial.

### 📊 D. Kalkulasi Finansial Otomatis & Laporan Laba Rugi
* **Deskripsi:** Menghitung laba-rugi operasional bulanan secara *real-time*.
* **Keunggulan:** Mengkalkulasi pendapatan kotor (*Gross Revenue*), Harga Pokok Penjualan (*COGS*) berdasarkan harga riil saat checkout (*price snapshot*), pengeluaran operasional harian, hingga persentase margin keuntungan bersih (*Net Profit Margin*).

### 📥 E. Ekspor Laporan Bisnis Multi-Sheet (Excel)
* **Deskripsi:** Memudahkan pelaporan eksternal untuk audit pajak, rapat internal, atau pengajuan modal ke investor.
* **Keunggulan:** Mengunduh berkas laporan bulanan terstruktur yang dipisahkan ke dalam tab lembar kerja yang rapi (*multi-sheet*): **Ringkasan**, **Laporan Harian**, **Per Kategori**, dan **Per Metode Pembayaran**.

---

## 🏗️ 3. Arsitektur Sistem & Skema Data

### 🖥️ Layered Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                      │
│                                                             │
│   Browser (Blade Views)            Mobile App / REST Client │
│   routes/web.php                   routes/api.php           │
└─────────────┬───────────────────────────────┬───────────────┘
              │                               │
              ▼                               ▼
┌─────────────────────────────────────────────────────────────┐
│                        SERVICE LAYER                        │
│                                                             │
│  - TransactionService (Atomic POS Checkout)                  │
│  - StockService (Single-entry Mutasi Stok)                  │
│  - InsightService (Engine Analitik Laba Rugi)               │
│  - ChatbotService (Intent Matching & AI Gemini RAG)         │
└─────────────────────────────┬───────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        DATA LAYER                           │
│                                                             │
│  Eloquent Models: Outlet, User, Product, Transaction, etc.  │
│  Multi-Tenancy Scopes: byOutlet(), inPeriod(), lowStock()   │
└─────────────────────────────────────────────────────────────┘
```

### 🗄️ Struktur Database
* **`outlets`**: Tabel induk *multi-tenant* toko.
* **`users`**: Akun pengguna terikat `outlet_id` dengan hak akses: `owner`, `admin`, `kasir`.
* **`products`**: Menyimpan persediaan barang, SKU, harga beli (HPP), dan harga jual.
* **`transactions` & `transaction_items`**: Pencatatan transaksi penjualan immutable dengan teknik *price snapshot*.
* **`expenses` & `expense_categories`**: Pencatatan biaya operasional outlet dilengkapi dengan berkas nota belanja.
* **`stock_movements`**: Log audit trail mutasi inventaris produk.

---

## ⚙️ 4. Panduan Instalasi & Penggunaan

Ikuti langkah-langkah di bawah ini untuk memasang dan menjalankan Umora di lingkungan lokal Anda:

### 📋 Persyaratan Sistem
* **PHP:** versi 8.1 atau lebih tinggi
* **Composer:** versi 2.x
* **MySQL:** versi 8.0 atau MariaDB 10.4+
* **Web Server:** Laragon, XAMPP, atau Laravel Valet

---

### 🛠️ Langkah-Langkah Pemasangan (Lokal)

#### 1. Kloning Repositori
```bash
git clone https://github.com/aliff2712/UMKM-MANAGEMENT.git
cd UMKM-MANAGEMENT
```

#### 2. Pasang Dependensi Composer
```bash
composer install
```

#### 3. Buat Berkas Konfigurasi `.env`
Salin template berkas konfigurasi bawaan dan hasilkan kunci aplikasi unik:
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Konfigurasi Database & API Key
Buka berkas `.env` baru Anda, lalu sesuaikan koneksi database dan masukkan kunci API Gemini untuk fitur Chatbot AI:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umora_umkm
DB_USERNAME=root
DB_PASSWORD=

# Kunci API Gemini (Opsional, untuk Asisten AI)
GEMINI_API_KEY=isi_kunci_gemini_anda_di_sini
```

> [!NOTE]
> Pastikan Anda telah membuat database kosong bernama `umora_umkm` di phpMyAdmin atau DBMS pilihan Anda sebelum melanjutkan ke langkah berikutnya.

#### 5. Jalankan Migrasi & Seeder Data
Jalankan migrasi tabel database dan masukkan data uji coba default (Owner, Admin, Kasir, Kategori, Produk, dan Transaksi):
```bash
php artisan migrate --seed
```

#### 6. Hubungkan Folder Penyimpanan Nota (*Storage Link*)
Buat pintasan tautan untuk mengizinkan akses berkas nota belanja produk dan pengeluaran ke folder publik:
```bash
php artisan storage:link
```

#### 7. Jalankan Server Lokal
Nyalakan server bawaan Laravel:
```bash
php artisan serve
```
Aplikasi kasir Umora kini dapat diakses melalui browser pada alamat: **`http://localhost:8000`**

---

### 🔑 Akun Uji Coba Default (Demo Credentials)
Setelah menjalankan perintah seeder, Anda dapat masuk ke aplikasi menggunakan akun bawaan berikut (Kata sandi untuk seluruh akun adalah **`password`**):

| Peran (Role) | Email Login | Hak Akses Utama |
|---|---|---|
| **Owner (Pemilik)** | `owner@toko.com` | Mengakses AI Chatbot, Keuangan Laba & Rugi, Grafik Insights, dan Ekspor Laporan. |
| **Admin (Manajer)** | `admin@toko.com` | Mengelola data produk, memasukkan biaya operasional, dan mutasi stok barang. |
| **Kasir (Frontliner)**| `kasir@toko.com` | Membuka kasir POS, memproses transaksi belanja, dan mencetak struk transaksi. |

---

## 🤝 5. Panduan Kontribusi

Kami sangat menyambut baik kontribusi dari komunitas untuk membuat Umora menjadi platform digitalisasi UMKM terbaik di Indonesia!

### 🛠️ Alur Kerja Kontribusi (Git Flow)
1. **Fork** repositori ini ke akun GitHub Anda.
2. Buat branch baru untuk pengerjaan fitur/perbaikan Anda:
   ```bash
   git checkout -b feature/nama-fitur-baru
   ```
3. Lakukan commit perubahan Anda secara teratur dengan format pesan commit yang rapi.
4. Lakukan push branch tersebut ke akun fork Anda:
   ```bash
   git push origin feature/nama-fitur-baru
   ```
5. Buka repositori asli dan ajukan **Pull Request (PR)** baru dengan menjelaskan detail perubahan yang Anda buat.

### 📝 Aturan Penulisan Pesan Commit (*Conventional Commits*)
Untuk menjaga kerapian riwayat git, gunakan konvensi penulisan pesan commit berikut:
* `feat: ...` — Untuk penambahan fitur baru (contoh: `feat: tambah popup modal QRIS di POS`).
* `fix: ...` — Untuk perbaikan kesalahan/bug (contoh: `fix: perbaikan kalkulasi total biaya di profit-loss`).
* `refactor: ...` — Untuk perubahan struktur kode tanpa mengubah fungsionalitas (contoh: `refactor: optimasi query relasi produk`).
* `docs: ...` — Untuk pembaruan berkas dokumentasi (contoh: `docs: update panduan instalasi di readme`).
* `style: ...` — Untuk perapian tampilan visual, format teks, atau CSS (contoh: `style: perbaiki responsivitas tabel`).

---

## 📡 6. API Reference (Dual Interface)

Semua endpoint API di bawah `/api/v1/` mewajibkan otentikasi menggunakan token pembawa (*Bearer Token*) dari Laravel Sanctum yang diperoleh setelah melakukan login:
```
Authorization: Bearer {your_sanctum_token}
Accept: application/json
```

### 🔐 Modul Autentikasi
* **`POST /api/v1/login`** — Otentikasi pengguna dan pembuatan token.
  * *Request Body:* `{"email": "owner@toko.com", "password": "password"}`
* **`POST /api/v1/logout`** — Menghapus token aktif pengguna saat ini.

### 📈 Modul Insights & Chatbot
* **`GET /api/v1/insights`** — Mengambil ringkasan data analitik keuangan dan stok outlet.
* **`GET /api/v1/insights/sales`** — Mengambil data tren penjualan berdasar periode.
* **`POST /api/v1/chatbot/ask`** — Mengirimkan pertanyaan teks analisis bisnis ke AI Umora.
  * *Request Body:* `{"question": "Bagaimana profitabilitas outlet saya bulan ini?"}`

### 🧾 Modul POS & Transaksi
* **`GET /api/v1/transactions`** — Riwayat seluruh transaksi di outlet saat ini.
* **`POST /api/v1/transactions`** — Membuat transaksi kasir atomic baru (stok otomatis terpotong).
  * *Request Body:*
    ```json
    {
      "payment_method": "qris",
      "paid_amount": 75000,
      "discount_amount": 5000,
      "items": [
        { "product_id": 1, "qty": 2 },
        { "product_id": 4, "qty": 1 }
      ]
    }
    ```

---

<p align="center">
  <strong>Umora — Empowering Indonesian MSMEs to Scale Up Digitally</strong><br>
  Built with ❤️ for a better business ecosystem.
</p>
