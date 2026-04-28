<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Package;
use Carbon\Carbon;

class PelangganCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path ke file CSV. 
        // Silakan taruh file pelanggan.csv di folder storage/app/ atau ubah path di bawah ini.
        $csvPath = storage_path('app/pelanggan.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan di: {$csvPath}");
            $this->command->info("Tolong buat file pelanggan.csv dengan format kolom (header):");
            $this->command->info("username,password,full_name,whatsapp_number,address,profile");
            return;
        }

        $file = fopen($csvPath, "r");
        
        // Membaca baris pertama sebagai Header
        $header = fgetcsv($file, 1000, ",");
        
        // Pastikan header di-trim dari whitespace/BOM
        $header = array_map(function($val) {
            return strtolower(trim($val));
        }, $header);

        $count = 0;

        while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            
            // Skip baris jika username kosong
            if (empty(trim($row['username'] ?? ''))) {
                continue;
            }

            // Cari package berdasarkan 'Paket' atau 'Profile Mikrotik'
            $packageId = null;
            $packageName = trim($row['paket'] ?? ($row['profile mikrotik'] ?? ''));
            if (!empty($packageName)) {
                $package = Package::where('name', 'LIKE', '%' . $packageName . '%')->first();
                $packageId = $package ? $package->id : null;
            }

            // Jika packageId masih null, pakai Package pertama
            if (!$packageId) {
                $firstPackage = Package::first();
                $packageId = $firstPackage ? $firstPackage->id : null;
            }

            // Gunakan ID Pelanggan dari CSV jika ada, atau generate
            $customerCode = trim($row['id pelanggan'] ?? '');
            $prefix = 'CST';
            if (empty($customerCode)) {
                $lastCustomer = Customer::where('code_prefix', $prefix)->orderBy('id', 'desc')->first();
                $nextNumber = $lastCustomer ? intval(substr($lastCustomer->customer_code, 4)) + 1 : 1;
                $customerCode = $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            // Bersihkan nomor WA
            $wa = $row['no whatsapp'] ?? '';
            $wa = preg_replace('/\D/', '', $wa);
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } elseif (str_starts_with($wa, '8')) {
                $wa = '62' . $wa;
            }

            // Insert atau Update berdasarkan Username
            Customer::updateOrCreate(
                ['username' => trim($row['username'])],
                [
                    'customer_code'   => $customerCode,
                    'code_prefix'     => $prefix,
                    'full_name'       => trim($row['nama lengkap'] ?? $row['username']),
                    'whatsapp_number' => $wa,
                    'address'         => trim($row['alamat'] ?? 'Jawa Tengah'),
                    'email'           => trim($row['email'] ?? ($row['username'] . '@djavva.net')),
                    'password'        => Hash::make(trim($row['password pppoe'] ?? '123456')),
                    'password_plain'  => trim($row['password pppoe'] ?? '123456'),
                    'package_id'      => $packageId,
                    'mikrotik_profile'=> trim($row['profile mikrotik'] ?? 'default'),
                    'status'          => 'active',
                    'active_since'    => Carbon::now()->startOfMonth(),
                    'billing_cycle'   => 1,
                    'is_synced'       => true,
                ]
            );

            $count++;
        }
        
        fclose($file);
        
        $this->command->info("Seeder Selesai! Berhasil memproses {$count} pelanggan ke database.");
    }
}
