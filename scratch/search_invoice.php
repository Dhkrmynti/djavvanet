<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$search = '2026041365';
$invoices = \App\Models\Invoice::where('invoice_number', 'like', "%$search%")->get();
foreach ($invoices as $inv) {
    echo "Number: " . $inv->invoice_number . " | Status: " . $inv->status . "\n";
}
if ($invoices->isEmpty()) {
    echo "No matching invoices found for '$search'\n";
    // list last 5 invoices
    echo "\nLast 5 invoices:\n";
    foreach (\App\Models\Invoice::latest()->take(5)->get() as $inv) {
        echo "Number: " . $inv->invoice_number . " | Status: " . $inv->status . "\n";
    }
}
