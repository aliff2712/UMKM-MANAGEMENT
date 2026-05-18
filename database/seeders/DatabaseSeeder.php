<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Outlet utama ──────────────────────────────────────────
        $outletId = DB::table('outlets')->insertGetId([
            'name'       => 'Toko Utama',
            'address'    => 'Jl. Contoh No. 1',
            'phone'      => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── 2. Users (1 per role) ────────────────────────────────────
        DB::table('users')->insert([
            [
                'outlet_id'  => $outletId,
                'name'       => 'Owner Utama',
                'email'      => 'owner@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'owner',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id'  => $outletId,
                'name'       => 'Admin Toko',
                'email'      => 'admin@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id'  => $outletId,
                'name'       => 'Kasir Toko',
                'email'      => 'kasir@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ── 3. Kategori produk ───────────────────────────────────────
        DB::table('categories')->insert([
            ['name' => 'Minuman',    'description' => 'Produk minuman',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Makanan',    'description' => 'Produk makanan',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Snack',      'description' => 'Camilan dan snack',      'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kebersihan', 'description' => 'Produk kebersihan',      'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya',    'description' => 'Produk kategori lain',   'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 4. Kategori pengeluaran ──────────────────────────────────
        DB::table('expense_categories')->insert([
            ['name' => 'Listrik',      'description' => 'Tagihan listrik',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air',          'description' => 'Tagihan air',              'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gaji',         'description' => 'Gaji karyawan',            'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sewa',         'description' => 'Biaya sewa tempat',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bahan Baku',   'description' => 'Pembelian bahan baku',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transportasi', 'description' => 'Biaya transportasi',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya',      'description' => 'Pengeluaran lain-lain',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
