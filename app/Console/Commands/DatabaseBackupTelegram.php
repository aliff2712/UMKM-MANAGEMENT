<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PDO;
use ZipArchive;

class DatabaseBackupTelegram extends Command
{
    // Nama perintah Artisan yang akan dipanggil di terminal
    // Jalankan dengan: php artisan db:backup-telegram
    protected $signature = 'db:backup-telegram';

    // Deskripsi singkat perintah
    protected $description = 'Mencadangkan database ke format ZIP dan mengirimkannya secara otomatis ke Telegram Owner';

    public function handle()
    {
        $this->info('Memulai pencadangan database...');

        // 1. Ambil data konfigurasi database dari file .env secara aman
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        $host     = $dbConfig['host'];
        $port     = $dbConfig['port'];
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];

        // Ambil token Telegram dari file .env
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId   = env('TELEGRAM_CHAT_ID');

        // Validasi: Pastikan token Telegram sudah diisi di .env
        if (!$botToken || !$chatId) {
            $this->error('Gagal: TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum diisi di berkas .env!');
            return Command::FAILURE;
        }

        // Siapkan nama file SQL dan ZIP berdasarkan tanggal hari ini
        $dateStr = now()->format('Y-m-d_H-i-s');
        $sqlFileName = "backup-{$database}-{$dateStr}.sql";
        $zipFileName = "backup-{$database}-{$dateStr}.zip";

        // Buat folder 'backups' di dalam storage/app jika belum ada
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        $sqlPath = storage_path("app/backups/{$sqlFileName}");
        $zipPath = storage_path("app/backups/{$zipFileName}");

        try {
            // 2. PROSES BACKUP (Pure PHP Generator - Tanpa butuh mysqldump.exe)
            $this->info('Membaca struktur dan data tabel...');
            
            // Koneksi ke database menggunakan PDO
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Buka file kosong untuk menulis query SQL
            $file = fopen($sqlPath, 'w');
            
            // Tulis header awal file SQL
            fwrite($file, "-- Umora Auto-Backup Database\n");
            fwrite($file, "-- Dibuat tanggal: " . now()->toDateTimeString() . "\n\n");
            fwrite($file, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Ambil seluruh nama tabel yang ada di database
            $tables = [];
            $query = $pdo->query('SHOW TABLES');
            while ($row = $query->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            // Loop setiap tabel untuk diekspor struktur dan datanya
            foreach ($tables as $table) {
                // Tulis komentar nama tabel
                fwrite($file, "\n-- ------------------------------------------------------\n");
                fwrite($file, "-- Struktur tabel untuk: `{$table}`\n");
                fwrite($file, "-- ------------------------------------------------------\n");

                // Ambil query pembentukan tabel (CREATE TABLE)
                $createTableQuery = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                fwrite($file, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($file, $createTableQuery['Create Table'] . ";\n\n");

                // Ambil seluruh isi data dari tabel tersebut
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();
                
                if (count($rows) > 0) {
                    fwrite($file, "-- Data untuk tabel: `{$table}`\n");
                    
                    foreach ($rows as $row) {
                        // Bersihkan nilai data agar aman saat dimasukkan ke query SQL
                        $escapedValues = array_map(function ($value) use ($pdo) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return $pdo->quote($value);
                        }, $row);

                        // Susun perintah INSERT INTO
                        $insertQuery = "INSERT INTO `{$table}` VALUES (" . implode(', ', $escapedValues) . ");\n";
                        fwrite($file, $insertQuery);
                    }
                }
            }

            // Tulis penutup file SQL
            fwrite($file, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($file);

            $this->info('File SQL sukses dibuat. Memulai kompresi ke berkas ZIP...');

            // 3. KOMPRESI KE ZIP (Menghemat bandwidth internet saat upload)
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // Masukkan file SQL ke dalam ZIP, lalu tutup
                $zip->addFile($sqlPath, $sqlFileName);
                $zip->close();
            } else {
                throw new \Exception('Gagal membuat berkas kompresi ZIP!');
            }

            $this->info('Berkas ZIP berhasil dikompres. Mengirimkan ke Telegram...');

            // 4. KIRIM KE TELEGRAM (Menggunakan API sendDocument)
            $response = Http::timeout(60)
                ->attach('document', file_get_contents($zipPath), $zipFileName)
                ->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                    'chat_id'    => $chatId,
                    'caption'    => "💾 *BACKUP DATABASE SUKSES!*\n\n🏪 UMKM: *" . config('app.name') . "*\n🗄️ Database: `{$database}`\n📅 Tanggal: " . now()->translatedFormat('d F Y (H:i:s)'),
                    'parse_mode' => 'Markdown',
                ]);

            // Cek apakah Telegram berhasil menerima dokumen
            if ($response->successful()) {
                $this->info('Pencadangan sukses dikirimkan ke Telegram!');
            } else {
                $this->error('Gagal mengirim ke Telegram. Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat proses backup: ' . $e->getMessage());
        } finally {
            // 5. BERSIHKAN FILE SEMENTARA (Agar harddisk laptop kasir tidak penuh)
            if (file_exists($sqlPath)) {
                unlink($sqlPath);
            }
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            $this->info('File cadangan lokal yang sementara telah dibersihkan.');
        }

        return Command::SUCCESS;
    }
}
