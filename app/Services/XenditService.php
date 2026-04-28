<?php

namespace App\Services;

use App\Models\XenditSetting;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected $apiKey;
    protected $mode;
    protected $isActive;

    public function __construct()
    {
        $settings = XenditSetting::first();
        if ($settings) {
            $this->apiKey = $settings->api_key;
            $this->mode = $settings->mode;
            $this->isActive = $settings->is_active;
        }
    }

    public function createInvoice(Invoice $invoice, $feeName = 'Biaya Admin', $feeAmount = 0, $paymentMethodCode = null)
    {
        if (!$this->isActive || !$this->apiKey) {
            throw new \Exception('Xendit payment is not configured or active.');
        }

        if ($feeAmount <= 0) {
            $settings = XenditSetting::first();
            $feeAmount = $settings->admin_fee ?? 0;
            $feeName = 'Biaya Admin';
        }

        $totalWithFee = (int) $invoice->total_amount + (int) $feeAmount;
        $endpoint = 'https://api.xendit.co/v2/invoices';

        $payload = [
            'external_id' => 'INV-' . $invoice->id . '-' . time(),
            'amount' => $totalWithFee,
            'description' => 'Pembayaran Tagihan Internet #' . $invoice->invoice_number,
            'customer' => [
                'given_names' => $invoice->customer->full_name,
                'mobile_number' => $invoice->customer->whatsapp_number,
                'email' => $invoice->customer->email ?? 'customer@djavva.net',
            ],
            'success_redirect_url' => route('portal.dashboard'),
            'failure_redirect_url' => route('portal.dashboard'),
            'currency' => 'IDR',
            'items' => [
                [
                    'name' => $invoice->customer->package->name,
                    'quantity' => 1,
                    'price' => (int) $invoice->total_amount,
                ]
            ]
        ];

        if ($paymentMethodCode) {
            if ($paymentMethodCode === 'va') {
                $payload['payment_methods'] = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI', 'SAHABAT_SAMPOERNA'];
            } elseif ($paymentMethodCode === 'qris') {
                $payload['payment_methods'] = ['QRIS'];
            } elseif ($paymentMethodCode === 'retailAlfamart' || (str_contains(strtolower($feeName), 'alfamart') && $paymentMethodCode === 'retail')) {
            } elseif ($paymentMethodCode === 'retailIndomaret' || (str_contains(strtolower($feeName), 'indomaret') && $paymentMethodCode === 'retail')) {
            } elseif ($paymentMethodCode === 'ewallet') {
                $payload['payment_methods'] = ['OVO', 'DANA', 'LINKAJA', 'SHOPEEPAY'];
            }
        }

        if ($feeAmount > 0) {
            $payload['items'][] = [
                'name' => $feeName,
                'quantity' => 1,
                'price' => (int) $feeAmount,
            ];
        }

        Log::info('Xendit Invoice Payload', [
            'invoice_id' => $invoice->id,
            'paymentMethodCode' => $paymentMethodCode,
            'payload' => $payload
        ]);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->post('https://api.xendit.co/v2/invoices', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Xendit Invoice Creation Failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'invoice_id' => $invoice->id
        ]);

        $errorData = $response->json();
        throw new \Exception('Gagal membuat invoice Xendit: ' . ($errorData['message'] ?? 'Unknown Error'));
    }

    public function validateCallbackToken($token)
    {
        $settings = XenditSetting::first();
        return $settings && $settings->callback_token === $token;
    }
}
