<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\XenditSetting;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    public function showPublicPayment($hash)
    {
        $invoice = Invoice::where('payment_hash', $hash)->with('customer.package')->firstOrFail();

        if ($invoice->status === 'paid' || $invoice->status === 'paid_cash') {
            return view('portal.payment_status', [
                'status' => 'success',
                'message' => 'Tagihan ini sudah lunas. Terima kasih!',
                'invoice' => $invoice
            ]);
        }

        $fees = \App\Models\XenditFee::where('is_active', true)->get();

        return view('portal.payment', compact('invoice', 'fees'));
    }

    public function checkout(Request $request, $hash)
    {
        $validated = $request->validate([
            'fee_id' => ['required', 'integer', 'exists:xendit_fees,id'],
        ]);

        $invoice = Invoice::where('payment_hash', $hash)->firstOrFail();

        if ($invoice->status !== 'unpaid' && $invoice->status !== 'overdue') {
            return back()->with('error', 'Tagihan ini sudah terbayar atau tidak valid.');
        }

        $fee = \App\Models\XenditFee::query()
            ->whereKey($validated['fee_id'])
            ->where('is_active', true)
            ->first();

        if (!$fee) {
            return back()->with('error', 'Metode pembayaran tidak tersedia. Silakan pilih metode lain.');
        }

        $feeAmount = $fee->calculateFee($invoice->total_amount);
        $feeName = 'Biaya Admin (' . $fee->name . ')';
        $paymentMethodCode = $fee->code;

        try {
            $cacheKey = "xendit_checkout:{$invoice->id}:{$fee->id}";
            $lock = Cache::lock("{$cacheKey}:lock", 15);

            $invoiceUrl = $lock->block(5, function () use ($cacheKey, $invoice, $feeName, $feeAmount, $paymentMethodCode) {
                return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($invoice, $feeName, $feeAmount, $paymentMethodCode) {
                    $xenditInvoice = $this->xenditService->createInvoice($invoice, $feeName, $feeAmount, $paymentMethodCode);

                    return $xenditInvoice['invoice_url']
                        ?? throw new \RuntimeException('Xendit response did not contain an invoice URL.');
                });
            });

            return redirect()->away($invoiceUrl);
        } catch (\Throwable $e) {
            Log::error('Checkout failed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_hash' => $hash,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal memproses pembayaran. Silakan coba kembali beberapa saat lagi.');
        }
    }

    public function callback(Request $request)
    {
        $xenditXToken = $request->header('x-callback-token');
        
        if (!$this->xenditService->validateCallbackToken($xenditXToken)) {
            Log::warning('Invalid Xendit callback token received.');
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $data = $request->all();
        Log::info('Xendit Callback Received', $data);

        $externalId = $data['external_id'] ?? '';
        $status = $data['status'] ?? '';

        if (strpos($externalId, 'INV-') === 0) {
            $parts = explode('-', $externalId);
            $invoiceId = $parts[1] ?? null;

            if ($invoiceId && $status === 'PAID') {
                $invoice = Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid_gateway') {
                    $invoice->update([
                        'status' => 'paid_gateway',
                        'paid_at' => now(),
                        'payment_method' => $data['payment_method'] ?? 'XENDIT',
                        'payment_channel' => $data['payment_channel'] ?? 'ONLINE',
                    ]);
                    
                    Log::info('Invoice marked as PAID_GATEWAY via Xendit', ['invoice_id' => $invoiceId]);

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
                                }
                            }
                            Log::info('Customer auto-restored after payment', ['customer_id' => $customer->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to auto-restore customer after payment: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
