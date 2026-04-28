<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Package;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::firstOrCreate(
            ['email' => 'admin@djavva.net'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Paket Internet
        $packages = [
            [
                'name'             => 'DJAVVA Home 10',
                'profile'          => 'Home',
                'monthly_price'    => 150000,
                'installation_fee' => null,
                'description'      => 'Cocok untuk kebutuhan internet dasar keluarga: browsing, sosmed, dan streaming ringan.',
                'features'         => ['Kecepatan hingga 10 Mbps', 'Kuota Tanpa Batas (FUP)', 'Router Dipinjamkan', 'Gratis Pemasangan'],
                'badge'            => 'Termurah',
                'sort_order'       => 1,
                'is_active'        => true,
            ],
            [
                'name'             => 'DJAVVA Home 20',
                'profile'          => 'Home',
                'monthly_price'    => 225000,
                'installation_fee' => null,
                'description'      => 'Ideal untuk keluarga modern dengan banyak perangkat terhubung.',
                'features'         => ['Kecepatan hingga 20 Mbps', 'Kuota Tanpa Batas (FUP)', 'Router Dual-Band', 'Gratis Kabel 20m'],
                'badge'            => 'Paling Laris',
                'sort_order'       => 2,
                'is_active'        => true,
            ],
            [
                'name'             => 'DJAVVA Bisnis 50',
                'profile'          => 'Bisnis',
                'monthly_price'    => 450000,
                'installation_fee' => 100000,
                'description'      => 'Didesain khusus untuk UKM, kafe, dan kantor kecil dengan prioritas koneksi stabil.',
                'features'         => ['Kecepatan simetris 50 Mbps', 'Prioritas Bandwidth', 'Bantuan CS 24/7', 'IP Publik Dinamis'],
                'badge'            => 'Tercepat',
                'sort_order'       => 3,
                'is_active'        => true,
            ]
        ];

        foreach ($packages as $pkg) {
            Package::firstOrCreate(['name' => $pkg['name']], $pkg);
        }

        // Ambil ID paket untuk dipakai di factory/seeder berikutnya
        $pkgHomeIds = Package::where('profile', 'Home')->pluck('id')->toArray();
        $pkgBizIds  = Package::where('profile', 'Bisnis')->pluck('id')->toArray();

        // 3. Customers (Pelanggan Aktif) - Sekarang langsung tanpa Registrations
        $faker = \Faker\Factory::create('id_ID');

        for ($i = 1; $i <= 10; $i++) {
            $isBiz = $i % 4 === 0; // Setiap pelanggan ke-4 adalah pelanggan bisnis
            $packageId = $faker->randomElement($isBiz ? $pkgBizIds : $pkgHomeIds);
            
            Customer::firstOrCreate(
                ['customer_code' => 'DJV-CUST-' . str_pad($i, 4, '0', STR_PAD_LEFT)],
                [
                    'package_id'       => $packageId,
                    'username'         => 'user' . $i,
                    'code_prefix'      => 'DJV',
                    'full_name'        => $faker->name,
                    'whatsapp_number'  => '08' . $faker->randomNumber(9, true),
                    'email'            => 'customer'.$i.'@example.com',
                    'password'         => Hash::make('password'),
                    'password_plain'   => 'password',
                    'address'          => $faker->address,
                    'latitude'         => $faker->latitude(-7.06, -6.97),
                    'longitude'        => $faker->longitude(108.47, 108.55),
                    'active_since'     => $faker->dateTimeBetween('-1 year', 'now'),
                    'billing_cycle'    => $faker->randomElement([1, 5, 10, 15, 20]),
                    'status'           => $faker->randomElement(['active', 'active', 'active', 'suspended', 'terminated']),
                ]
            );
        }
    }
}
