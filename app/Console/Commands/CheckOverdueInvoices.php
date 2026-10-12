<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\MikrotikSetting;
use App\Services\MikrotikService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek tagihan yang melewati jatuh tempo dan isolir pelanggan secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $waService)
    {
        $today = now()->toDateString();
        
        // Cari tagihan UNPAID yang jatuh temponya < hari ini
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', $today)
            ->with(['customer', 'customer.package'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('Tidak ada tagihan jatuh tempo yang ditemukan hari ini.');
            return;
        }

        $this->info('Menemukan ' . $overdueInvoices->count() . ' tagihan jatuh tempo. Memulai proses isolir...');
        Log::info('Auto-Isolation Process Started', ['count' => $overdueInvoices->count()]);

        $settings = MikrotikSetting::where('is_active', true)->first();
        $mikrotik = null;
        if ($settings) {
            $mikrotik = new MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
        }

        $connected = false;
        if ($mikrotik && $mikrotik->connect()) {
            $connected = true;
        } else {
            $this->warn('Gagal terhubung ke Mikrotik. Isolir hanya akan dilakukan di sisi database.');
            Log::warning('Auto-Isolation: Mikrotik connection failed. Proceeding with database-only update.');
        }

        foreach ($overdueInvoices as $invoice) {
            $customer = $invoice->customer;
            if (!$customer) continue;

            $this->info("Memproses: {$customer->full_name} ({$invoice->invoice_number})");

            // 1. Update Invoice Status
            $invoice->update(['status' => 'overdue']);

            // 2. Update Customer Status (jika masih active)
            if ($customer->status === 'active') {
                $customer->update(['status' => 'suspend']);
            }

            // 3. Update Mikrotik Profile jika user memiliki username
            if ($connected && $customer->username) {
                try {
                    $mikrotik->setPPPoESecretProfile($customer->username, $settings->isolir_profile);
                    $mikrotik->removeActivePPPoE($customer->username);
                    $this->info(" - Berhasil diisolir di Mikrotik.");
                } catch (\Exception $e) {
                    $this->error(" - Gagal isolir di Mikrotik: " . $e->getMessage());
                    Log::error('Auto-Isolation failed for customer: ' . $customer->username, ['error' => $e->getMessage()]);
                }
            }

            // 4. Kirim WA Notifikasi
            try {
                $period = Carbon::parse($invoice->period_start)->translatedFormat('F Y');
                $link = route('payment.public', $invoice->payment_hash);
                $msg = "*DJAVVA NETWORK — ISOLIR*\n\n" .
                       "Yth. Bapak/Ibu *{$customer->full_name}*\n\n" .
                       "Layanan internet Anda sementara kami *ISOLIR* (nonaktifkan) karena tagihan periode *{$period}* belum terbayar.\n\n" .
                       "Segera lakukan pembayaran melalui link di bawah ini agar internet Anda *otomatis aktif kembali*:\n\n" .
                       "{$link}\n\n" .
                       "Abaikan pesan ini jika Anda sudah melakukan pembayaran. Terima kasih.";
                
                $waService->sendMessage($customer->whatsapp_number, $msg);
                $this->info(" - Notifikasi WA terkirim.");
            } catch (\Exception $e) {
                $this->error(" - Gagal kirim WA: " . $e->getMessage());
            }

            // 5. Jeda 1 detik agar Mikrotik tidak overload
            // Jeda acak agar tidak dianggap spam masal oleh WhatsApp
            sleep(rand(3, 7));
        }

        if ($connected) {
            $mikrotik->disconnect();
            Cache::forget('mikrotik_pppoe_secrets');
        }

        $this->info('Proses isolir otomatis selesai.');
        Log::info('Auto-Isolation Process Finished');
    }
}
