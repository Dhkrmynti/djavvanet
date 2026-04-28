<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Support\CustomerExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BackupCustomersCommand extends Command
{
    protected $signature = 'customers:backup {--force : Jalankan backup tanpa cek jadwal pengaturan}';

    protected $description = 'Generate backup data customer (CSV) dan kirim ke email sesuai pengaturan';

    public function handle(): int
    {
        $mode = AppSetting::get('customer_backup_mode', 'manual');
        $frequency = AppSetting::get('customer_backup_frequency', 'disabled');
        $weekday = AppSetting::get('customer_backup_weekday', 'monday');
        $hour = (int) AppSetting::get('customer_backup_hour', '06');
        $force = (bool) $this->option('force');

        if (!$force) {
            if ($mode === 'manual') {
                $this->info('Mode backup diset ke Manual. Backup otomatis dilewati.');
                return self::SUCCESS;
            }

            if ($frequency === 'disabled') {
                $this->info('Backup customer otomatis sedang nonaktif.');
                return self::SUCCESS;
            }

            if ($frequency === 'weekly' && now()->englishDayOfWeek !== ucfirst($weekday)) {
                $this->info('Bukan hari backup mingguan. Backup dilewati.');
                return self::SUCCESS;
            }

            if ((int) now()->hour !== $hour) {
                $this->info("Belum waktunya backup (jam saat ini: " . now()->hour . ", jadwal: {$hour}). Dilewati.");
                return self::SUCCESS;
            }
        }

        try {
            $backup = CustomerExport::storeCsv();
            $this->info("Backup dibuat: {$backup['path']} ({$backup['count']} customer)");

            $emails = collect(explode(',', (string) AppSetting::get('customer_backup_emails', '')))
                ->map(fn ($email) => trim($email))
                ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                ->values();

            if ($emails->isEmpty()) {
                $this->warn('Backup selesai, tapi email tujuan belum diatur.');
                Log::warning('Customer backup generated without recipient email.', ['path' => $backup['path']]);
                return self::SUCCESS;
            }

            foreach ($emails as $email) {
                Mail::raw(
                    "Backup data customer otomatis terlampir.\n\nTanggal: " . now()->format('Y-m-d H:i:s') . "\nJumlah data: {$backup['count']}",
                    function ($message) use ($email, $backup) {
                        $message->to($email)
                            ->subject('Backup Data Customer')
                            ->attach($backup['absolute_path'], [
                                'as' => $backup['filename'],
                                'mime' => 'text/csv',
                            ]);
                    }
                );
            }

            $this->info('Backup email berhasil dikirim.');
            Log::info('Customer backup generated and emailed.', [
                'path' => $backup['path'],
                'recipients' => $emails->all(),
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Customer backup failed.', ['error' => $e->getMessage()]);
            $this->error('Backup customer gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

