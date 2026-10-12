<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = \App\Models\Invoice::where('invoice_number', 'INV-2026041365')->first();
if ($invoice) {
    echo "ID: " . $invoice->id . "\n";
    echo "Status: " . $invoice->status . "\n";
    echo "Payment Method: " . ($invoice->payment_method ?? 'N/A') . "\n";
    echo "Payment Channel: " . ($invoice->payment_channel ?? 'N/A') . "\n";
} else {
    echo "Invoice not found\n";
}
