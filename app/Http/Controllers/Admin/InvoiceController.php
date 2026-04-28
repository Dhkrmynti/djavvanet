<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\AppSetting;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $totalInvoices = Invoice::count();
        return view('admin.invoices.index', compact('totalInvoices'));
    }

    public function invoicesData(Request $request)
    {
        $query = Invoice::with(['customer', 'customer.package'])->select('invoices.*');

        // Period filter (server-side)
        if ($request->filled('period') && $request->period !== '') {
            $parts = explode('-', $request->period);
            if (count($parts) === 2) {
                $query->whereYear('period_start', $parts[0])
                      ->whereMonth('period_start', $parts[1]);
            }
        }

        return DataTables::eloquent($query)
            ->addColumn('customer_code', function (Invoice $inv) {
                return $inv->customer->customer_code ?? 'N/A';
            })
            ->addColumn('customer_name', function (Invoice $inv) {
                return $inv->customer->full_name ?? 'N/A';
            })
            ->addColumn('period_label', function (Invoice $inv) {
                return $inv->period_start ? Carbon::parse($inv->period_start)->translatedFormat('F Y') : 'N/A';
            })
            ->addColumn('raw_invoice', function (Invoice $inv) {
                return $inv->load(['customer', 'customer.package'])->toArray();
            })
            ->filterColumn('customer_code', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('customer_code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('full_name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('period_label', function ($query, $order) {
                $query->orderBy('period_start', $order);
            })
            ->toJson();
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'customer.package']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Tagihan ini sudah berstatus lunas.');
        }

        $invoice->update([
            'status' => 'paid',
        ]);

        try {
            $customer = $invoice->customer;
            if ($customer && $customer->status === 'suspend') {
                $customer->update(['status' => 'active']);
                
                $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
                if ($settings) {
                    $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
                    if ($mikrotik->connect()) {
                        $profile = $customer->mikrotik_profile;
                        if (!$profile && $customer->package_id) {
                            $package = \App\Models\Package::find($customer->package_id);
                            $profile = $package ? $package->profile : 'default';
                        }
                        $mikrotik->setPPPoESecretProfile($customer->username, $profile ?: 'default');
                        $mikrotik->removeActivePPPoE($customer->username);
                        $mikrotik->disconnect();
                        \Illuminate\Support\Facades\Cache::forget('mikrotik_pppoe_secrets');
                    }
                }
                Log::info('Customer auto-restored after manual payment link', ['customer_id' => $customer->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to auto-restore customer after manual payment: ' . $e->getMessage());
        }

        return back()->with('success', 'Tagihan berhasil ditandai sebagai Lunas. Pelanggan telah diaktifkan kembali jika sebelumnya terisolir.');
    }

    public function generateInvoices(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $count = 0;
        
        $targetMonth = (int) now()->month;
        $targetYear = (int) now()->year;

        if ($request->filled('period')) {
            $parts = explode('-', $request->period);
            if (count($parts) === 2) {
                $targetYear = (int) $parts[0];
                $targetMonth = (int) $parts[1];
            }
        }
        
        $targetPeriodDate = Carbon::create($targetYear, $targetMonth, 1)->startOfMonth();

        foreach ($customers as $customer) {
            $activeSince = $customer->active_since ? (clone $customer->active_since)->startOfMonth() : null;
            
            // Jika pelanggan belum aktif saat periode target berakhir, skip
            if (!$activeSince || $activeSince->gt($targetPeriodDate)) {
                continue;
            }

            // Loop dari bulan aktif sampai bulan yang dipilih (Scan-and-Fill)
            $currentScan = (clone $activeSince);
            while ($currentScan->lte($targetPeriodDate)) {
                $periodStart = (clone $currentScan)->startOfDay();
                $periodEnd = (clone $periodStart)->addMonth();
                $dueDate = (clone $targetPeriodDate)->addMonth()->setDay(25);

                $exists = Invoice::where('customer_id', $customer->id)
                    ->whereDate('period_start', $periodStart->toDateString())
                    ->exists();

                if (!$exists) {
                    $yearStr = $currentScan->year;
                    $monthStr = $currentScan->month;
                    $lastId = Invoice::max('id') ?? 0;
                    $newNumber = 'INV-' . $yearStr . str_pad($monthStr, 2, '0', STR_PAD_LEFT) . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                    
                    $monthlyPrice = $customer->package->monthly_price ?? 0;
                    $activeSinceOriginal = $customer->active_since;
                    
                    $amount = $monthlyPrice;
                    
                    // Logika Prorata hanya jika ini adalah bulan pertama aktif
                    if ($activeSinceOriginal && $activeSinceOriginal->gt($periodStart) && $activeSinceOriginal->lt($periodEnd)) {
                        $daysInMonth = 30; // Standar 30 hari sesuai permintaan sebelumnya
                        // Gunakan startOfDay agar hitungan harinya bulat dan predictable
                        $daysActive = $activeSinceOriginal->copy()->startOfDay()->diffInDays($periodEnd);
                        $calculatedAmount = floor(($monthlyPrice / $daysInMonth) * $daysActive);
                        $amount = min($monthlyPrice, $calculatedAmount);
                    }

                    Invoice::create([
                        'customer_id'    => $customer->id,
                        'invoice_number' => $newNumber,
                        'period_start'   => $periodStart->toDateString(),
                        'period_end'     => $periodEnd->toDateString(),
                        'amount'         => $amount,
                        'tax_amount'     => 0,
                        'total_amount'   => $amount, // tax 0
                        'due_date'       => $dueDate->toDateString(),
                        'status'         => 'unpaid',
                    ]);
                    $count++;
                }

                $currentScan->addMonth();
            }
        }

        return back()->with('success', "Berhasil memproses scan billing. {$count} tagihan baru berhasil dibuat.");
    }

    public function sendBulkWhatsApp(Request $request, WhatsAppService $waService)
    {
        $query = Invoice::with('customer')
            ->where('status', 'unpaid')
            ->where('is_wa_sent', false);

        if ($request->filled('period')) {
            $parts = explode('-', $request->period);
            if (count($parts) === 2) {
                $query->whereYear('period_start', $parts[0])
                      ->whereMonth('period_start', $parts[1]);
                $periodLabel = Carbon::create($parts[0], $parts[1], 1)->translatedFormat('F Y');
            }
        }

        $invoices = $query->get();
        $totalToProcess = $invoices->count();

        Log::info("Qualifying invoices for bulk WA: " . $totalToProcess . ($request->filled('period') ? " for period " . $request->period : " for ALL periods"));

        if ($totalToProcess === 0) {
            return back()->with('error', 'Tidak ada tagihan Belum Bayar yang ditemukan untuk dikirimkan.');
        }

        $count = 0;
        $skipped = 0;
        $invalid = 0;
        set_time_limit(0);

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            if (!$customer || !$customer->whatsapp_number) {
                $skipped++;
                continue;
            }

            $message = $this->buildWhatsAppMessage($invoice);

            if ($waService->sendMessage($customer->whatsapp_number, $message)) {
                $invoice->update([
                    'is_wa_sent' => true,
                    'wa_sent_at' => now()
                ]);
                $count++;
            } else {
                $invalid++;
            }

            sleep(3);
        }

        $statusMsg = "Berhasil mengirim {$count} pesan WhatsApp.";
        
        if ($skipped > 0 || $invalid > 0) {
            $details = [];
            if ($skipped > 0) $details[] = "{$skipped} nomor kosong";
            if ($invalid > 0) $details[] = "{$invalid} nomor tidak terhubung WA";
            
            $statusMsg .= " (" . implode(', ', $details) . " diloncati).";
        }

        return back()->with('success', $statusMsg);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'   => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status'   => 'required|in:unpaid,paid,paid_cash,overdue,cancelled',
        ]);

        $oldStatus = $invoice->status;
        $invoice->update([
            'amount'       => $validated['amount'],
            'tax_amount'   => 0,
            'total_amount' => $validated['amount'],
            'due_date'     => $validated['due_date'],
            'status'       => $validated['status'],
        ]);

        if ($oldStatus !== 'paid' && $validated['status'] === 'paid') {
            try {
                $customer = $invoice->customer;
                if ($customer && $customer->status === 'suspend') {
                    $customer->update(['status' => 'active']);
                    
                    $settings = \App\Models\MikrotikSetting::where('is_active', true)->first();
                    if ($settings) {
                        $mikrotik = new \App\Services\MikrotikService($settings->host, $settings->username, $settings->password, $settings->port);
                        if ($mikrotik->connect()) {
                            $profile = $customer->mikrotik_profile;
                            if (!$profile && $customer->package_id) {
                                $package = \App\Models\Package::find($customer->package_id);
                                $profile = $package ? $package->profile : 'default';
                            }
                            $mikrotik->setPPPoESecretProfile($customer->username, $profile ?: 'default');
                            $mikrotik->removeActivePPPoE($customer->username);
                            $mikrotik->disconnect();
                            \Illuminate\Support\Facades\Cache::forget('mikrotik_pppoe_secrets');
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to auto-restore customer after invoice edit: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(\App\Models\Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'customer.package']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('common.invoice-pdf', compact('invoice'));
        
        $filename = str_replace(['/', '\\'], '-', "Invoice_{$invoice->invoice_number}.pdf");
        return $pdf->download($filename);
    }

    public function sendWhatsApp(Invoice $invoice, WhatsAppService $waService)
    {
        $customer = $invoice->customer;
        if (!$customer || !$customer->whatsapp_number) {
            return back()->with('error', 'Nomor WhatsApp pelanggan tidak ditemukan.');
        }

        $message = $this->buildWhatsAppMessage($invoice);

        if ($waService->sendMessage($customer->whatsapp_number, $message)) {
            $invoice->update([
                'is_wa_sent' => true,
                'wa_sent_at' => now()
            ]);
            return back()->with('success', 'Pesan WhatsApp berhasil dikirim.');
        }

        return back()->with('error', 'Gagal mengirim pesan WhatsApp.');
    }

    private function buildWhatsAppMessage(Invoice $invoice): string
    {
        $customer = $invoice->customer;
        $period = Carbon::parse($invoice->period_start)->translatedFormat('F Y');
        $due = Carbon::parse($invoice->due_date)->translatedFormat('d M Y');
        $total = number_format($invoice->total_amount, 0, ',', '.');
        $link = route('payment.public', $invoice->payment_hash);

        $defaultTemplate = "*DJAVVA NETWORK*\n\n" .
            "Yth. Pelanggan / Dear Customer\n" .
            "*{customer_name}* (ID: *{customer_code}*)\n\n" .
            "Tagihan Anda periode *{period}* sebesar *Rp{total_amount}* mohon lakukan pembayaran sebelum *{due_date}* agar terhindar dari pemutusan internet atau isolir.\n\n" .
            "_Your internet bill for the period of *{period}* is *Rp{total_amount}*. Please make your payment before *{due_date}* to avoid internet disconnection or isolation._\n\n" .
            "Link pembayaran / Payment link:\n" .
            "{payment_link}\n\n" .
            "Terima Kasih / Thank You\n\n" .
            "Layanan Pelanggan / Customer Service\n" .
            "WhatsApp chat: 6282246468335\n" .
            "Email: djavvanetwork@gmail.com";

        $template = AppSetting::get('wa_invoice_template', $defaultTemplate);

        return strtr($template, [
            '{customer_name}'   => $customer->full_name ?? '-',
            '{customer_code}'   => $customer->customer_code ?? '-',
            '{address}'         => $customer->address ?? '-',
            '{customer_address}' => $customer->address ?? '-',
            '{package_name}'    => $customer->package->name ?? '-',
            '{package}'         => $customer->package->name ?? '-',
            '{customer_package}' => $customer->package->name ?? '-',
            '{package_price}'   => $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0',
            '{monthly_price}'   => $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0',
            '{period}'          => $period,
            '{due_date}'        => $due,
            '{total_amount}'    => $total,
            '{payment_link}'    => $link,
        ]);
    }
}
