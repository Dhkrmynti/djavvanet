<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CustomerExport
{
    public static function headings(): array
    {
        return [
            'ID',
            'ID Pelanggan',
            'Nama Lengkap',
            'Username',
            'Password PPPoE',
            'No WhatsApp',
            'Email',
            'Alamat',
            'Paket',
            'Profile Mikrotik',
            'Harga Paket',
            'Status',
            'Aktif Sejak',
            'Billing Cycle',
            'Sinkron Router',
            'Dibuat',
            'Diupdate',
        ];
    }

    public static function rows(): Collection
    {
        return Customer::with('package')
            ->orderBy('id')
            ->get()
            ->map(function (Customer $customer) {
                return [
                    $customer->id,
                    $customer->customer_code,
                    $customer->full_name,
                    $customer->username,
                    $customer->password_plain,
                    $customer->whatsapp_number,
                    $customer->email,
                    $customer->address,
                    $customer->package->name ?? '',
                    $customer->mikrotik_profile ?: ($customer->package->profile ?? ''),
                    $customer->package->monthly_price ?? 0,
                    $customer->status,
                    optional($customer->active_since)->format('Y-m-d'),
                    $customer->billing_cycle,
                    $customer->is_synced ? 'yes' : 'no',
                    optional($customer->created_at)->format('Y-m-d H:i:s'),
                    optional($customer->updated_at)->format('Y-m-d H:i:s'),
                ];
            });
    }

    public static function storeCsv(string $directory = 'backups/customers'): array
    {
        $rows = self::rows();
        $filename = 'customers-backup-' . now()->format('Ymd-His') . '.csv';
        $relativePath = trim($directory, '/') . '/' . $filename;

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, self::headings());
        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);

        Storage::disk('local')->put($relativePath, stream_get_contents($stream));
        fclose($stream);

        return [
            'path' => $relativePath,
            'absolute_path' => Storage::disk('local')->path($relativePath),
            'filename' => $filename,
            'count' => $rows->count(),
        ];
    }
}

