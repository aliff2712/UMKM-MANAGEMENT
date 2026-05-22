<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk data user dummy.
     */
    public function run(): void
    {
        // Pastikan minimal ada satu outlet
        $outlet = Outlet::first();
        
        if (!$outlet) {
            $outlet = Outlet::create([
                'name'       => 'Outlet Umora Pusat',
                'address'    => 'Jl. Digital Raya No. 101, Jakarta',
                'phone'      => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $outletId = $outlet->id;

        // 1. Akun Uji Coba Utama dengan Berbagai Role (Password: password)
        $primaryUsers = [
            [
                'outlet_id'  => $outletId,
                'name'       => 'Owner Umora',
                'email'      => 'owner@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'owner',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id'  => $outletId,
                'name'       => 'Admin Umora',
                'email'      => 'admin@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'outlet_id'  => $outletId,
                'name'       => 'Kasir Umora',
                'email'      => 'kasir@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun Non-Aktif untuk pengujian validasi status blokir saat login
            [
                'outlet_id'  => $outletId,
                'name'       => 'Kasir Non-Aktif',
                'email'      => 'nonaktif@toko.com',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'is_active'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($primaryUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // 2. Tambahan Dummy Users untuk Simulasi Skala Besar
        // 5 Kasir tambahan
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => 'kasir.dummy' . $i . '@toko.com'],
                [
                    'outlet_id'  => $outletId,
                    'name'       => 'Kasir Dummy ' . $i,
                    'password'   => Hash::make('password'),
                    'role'       => 'kasir',
                    'is_active'  => true,
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3 Admin tambahan
        for ($i = 1; $i <= 3; $i++) {
            User::updateOrCreate(
                ['email' => 'admin.dummy' . $i . '@toko.com'],
                [
                    'outlet_id'  => $outletId,
                    'name'       => 'Admin Dummy ' . $i,
                    'password'   => Hash::make('password'),
                    'role'       => 'admin',
                    'is_active'  => true,
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
