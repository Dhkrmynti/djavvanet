<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-generate monthly invoices for all active customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly invoice generation...');
        Log::info('Console: Starting invoices:generate-monthly');

        $customers = Customer::whereIn('status', ['active', 'suspend'])->get();
        $count = 0;

        $targetPeriodDate = now()->subMonth()->startOfMonth();
        $periodLabel = $targetPeriodDate->translatedFormat('F Y');

        foreach ($customers as $customer) {
            $activeSince = $customer->active_since ? Carbon::parse($customer->active_since)->startOfMonth() : null;

            if (!$activeSince || $activeSince->gt($targetPeriodDate)) {
                continue;
            }

            $periodStart = (clone $targetPeriodDate)->startOfDay();
            $periodEnd = (clone $periodStart)->addMonth();
            $dueDate = (clone $targetPeriodDate)->addMonth()->setDay(25);

            $exists = Invoice::where('customer_id', $customer->id)
                ->whereDate('period_start', $periodStart->toDateString())
                ->exists();

            if (!$exists) {
                $yearStr = $targetPeriodDate->year;
                $monthStr = $targetPeriodDate->month;
                $lastId = Invoice::max('id') ?? 0;
                $newNumber = 'INV-' . $yearStr . str_pad($monthStr, 2, '0', STR_PAD_LEFT) . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                $monthlyPrice = $customer->package->monthly_price ?? 0;
                $activeSinceOriginal = $customer->active_since ? Carbon::parse($customer->active_since) : null;

                $amount = $monthlyPrice;

                // Logika Prorata hanya jika ini adalah bulan pertama aktif
                if ($activeSinceOriginal && $activeSinceOriginal->gt($periodStart) && $activeSinceOriginal->lt($periodEnd)) {
                    $daysInMonth = 30;
                    $daysActive = $activeSinceOriginal->copy()->startOfDay()->diffInDays($periodEnd);
                    $calculatedAmount = floor(($monthlyPrice / $daysInMonth) * $daysActive);
                    $amount = min($monthlyPrice, $calculatedAmount);
                }

                Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $newNumber,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'amount' => $amount,
                    'tax_amount' => 0,
                    'total_amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    'status' => 'unpaid',
                ]);
                $count++;
            }
        }

        $this->info("Successfully generated {$count} invoices for {$periodLabel}.");
        Log::info("Console: Finished invoices:generate-monthly. Generated {$count} invoices.");
    }
}
