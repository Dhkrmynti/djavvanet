<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

// 1. Fix specific invoice mentioned by user
$inv = Invoice::where('invoice_number', 'INV-2026041365')->first();
if ($inv) {
    echo "Updating INV-2026041365 from {$inv->status} to paid_gateway...\n";
    $inv->update(['status' => 'paid_gateway']);
} else {
    echo "INV-2026041365 not found.\n";
}

// 2. Fix other potential gateway payments still using old 'paid' status
$others = Invoice::where('status', 'paid')->get();
$count = 0;
foreach ($others as $other) {
    // If it has payment_method 'XENDIT' or similar, it should be paid_gateway
    if (isset($other->payment_method) && (strtoupper($other->payment_method) === 'XENDIT' || $other->payment_method === 'ONLINE')) {
        $other->update(['status' => 'paid_gateway']);
        $count++;
    }
}

echo "Updated {$count} other invoices to paid_gateway.\n";
