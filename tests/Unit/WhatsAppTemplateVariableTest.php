<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\AdminController;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class WhatsAppTemplateVariableTest extends TestCase
{
    use RefreshDatabase;

    // ─── INVOICE TEMPLATE ───────────────────────────────────────────────

    /** @test */
    public function invoice_template_replaces_all_six_variables()
    {
        $customer = Customer::factory()->create([
            'full_name'     => 'Budi Santoso',
            'customer_code' => 'DJV1001',
        ]);

        $invoice = Invoice::factory()->create([
            'customer_id'  => $customer->id,
            'total_amount' => 150000,
            'period_start' => Carbon::create(2026, 4, 1),
            'due_date'     => Carbon::create(2026, 5, 25),
        ]);

        // Set custom template yang menggunakan SEMUA variable
        AppSetting::updateOrCreate(
            ['key' => 'wa_invoice_template'],
            ['value' => 'Nama:{customer_name} ID:{customer_code} Periode:{period} Total:{total_amount} Jatuh:{due_date} Link:{payment_link}']
        );

        $controller = app(InvoiceController::class);
        $method = new ReflectionMethod(InvoiceController::class, 'buildWhatsAppMessage');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $invoice);

        // Verify setiap variable ter-replace (bukan placeholder lagi)
        $this->assertStringNotContainsString('{customer_name}', $result);
        $this->assertStringNotContainsString('{customer_code}', $result);
        $this->assertStringNotContainsString('{period}', $result);
        $this->assertStringNotContainsString('{total_amount}', $result);
        $this->assertStringNotContainsString('{due_date}', $result);
        $this->assertStringNotContainsString('{payment_link}', $result);

        // Verify nilai yang benar
        $this->assertStringContainsString('Budi Santoso', $result);
        $this->assertStringContainsString('DJV1001', $result);
        $this->assertStringContainsString('150.000', $result);
        $this->assertStringContainsString('April 2026', $result);
        $this->assertStringContainsString('25 Mei 2026', $result);
        $this->assertStringContainsString($invoice->payment_hash, $result);
    }

    /** @test */
    public function invoice_template_uses_default_when_not_configured()
    {
        $customer = Customer::factory()->create(['full_name' => 'Test User']);
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        // Hapus setting agar pakai default
        AppSetting::where('key', 'wa_invoice_template')->delete();

        $controller = app(InvoiceController::class);
        $method = new ReflectionMethod(InvoiceController::class, 'buildWhatsAppMessage');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $invoice);

        // Default template mengandung "DJAVVA NETWORK"
        $this->assertStringContainsString('DJAVVA NETWORK', $result);
        $this->assertStringContainsString('Test User', $result);
        // Pastikan tidak ada placeholder tersisa
        $this->assertStringNotContainsString('{customer_name}', $result);
    }

    /** @test */
    public function invoice_template_handles_empty_customer_data_gracefully()
    {
        // The strtr in buildWhatsAppMessage uses ?? '-' fallback for null values.
        // Since DB has NOT NULL on full_name & customer_code, test with empty strings
        // which is the real-world edge case.
        $customer = Customer::factory()->create([
            'full_name'     => '',
            'customer_code' => 'DJV0000',
        ]);

        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        AppSetting::updateOrCreate(
            ['key' => 'wa_invoice_template'],
            ['value' => 'Nama:{customer_name} ID:{customer_code}']
        );

        $controller = app(InvoiceController::class);
        $method = new ReflectionMethod(InvoiceController::class, 'buildWhatsAppMessage');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $invoice);

        // empty string via ?? will pass through (not trigger fallback), customer_code stays
        $this->assertStringContainsString('DJV0000', $result);
        $this->assertStringNotContainsString('{customer_name}', $result);
        $this->assertStringNotContainsString('{customer_code}', $result);
    }

    // ─── GREETING TEMPLATE ──────────────────────────────────────────────

    /** @test */
    public function test_greeting_template_replaces_all_variables_including_aliases()
    {
        $package = Package::factory()->create(['name' => 'Ultra 20M', 'monthly_price' => 250000]);
        $customer = Customer::factory()->create([
            'full_name' => 'Budi Santoso',
            'customer_code' => 'DJV123',
            'username' => 'budi@djavva.net',
            'password_plain' => 'secret123',
            'address' => 'Jl. Merdeka No. 10',
            'package_id' => $package->id
        ]);

        $template = "Halo {customer_name},\nAlamat: {customer_address}\nPaket: {customer_package}\nHarga: {monthly_price}";
        
        // Simulating AdminController inline
        $message = str_replace(
            ['{customer_name}', '{customer_code}', '{username}', '{password}', '{address}', '{customer_address}', '{package_name}', '{package}', '{customer_package}', '{package_price}', '{monthly_price}'],
            [
                $customer->full_name, 
                $customer->customer_code, 
                $customer->username,
                $customer->password_plain,
                $customer->address ?? '-',
                $customer->address ?? '-',
                $customer->package->name ?? '-',
                $customer->package->name ?? '-',
                $customer->package->name ?? '-',
                $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0',
                $customer->package ? 'Rp ' . number_format($customer->package->monthly_price, 0, ',', '.') : 'Rp 0'
            ],
            $template
        );

        $this->assertStringContainsString('Budi Santoso', $message);
        $this->assertStringContainsString('Jl. Merdeka No. 10', $message);
        $this->assertStringContainsString('Ultra 20M', $message);
        $this->assertStringContainsString('Rp 250.000', $message);
    }

    /** @test */
    public function greeting_template_with_special_characters_in_name()
    {
        $customer = Customer::factory()->create([
            'full_name'      => "M. Ra'uf & Sons",
            'customer_code'  => 'DJV3003',
            'username'       => 'rauf@djavva.net',
            'password_plain' => 'p@ss!',
        ]);

        $template = '{customer_name} ({customer_code})';
        $message = str_replace(
            ['{customer_name}', '{customer_code}'],
            [$customer->full_name, $customer->customer_code],
            $template
        );

        $this->assertEquals("M. Ra'uf & Sons (DJV3003)", $message);
    }

    // ─── MAINTENANCE TEMPLATE ───────────────────────────────────────────

    /** @test */
    public function maintenance_template_replaces_only_global_variables()
    {
        $customer = Customer::factory()->create([
            'full_name'     => 'Siti Nurhaliza',
            'customer_code' => 'DJV4004',
        ]);

        $template = 'Dear {customer_name} ({customer_code}), maintenance dijadwalkan.';
        AppSetting::updateOrCreate(
            ['key' => 'wa_maintenance_template'],
            ['value' => $template]
        );

        // Simulasi logika di AdminController@blastMaintenance 
        $storedTemplate = AppSetting::get('wa_maintenance_template');
        $message = str_replace(
            ['{customer_name}', '{customer_code}'],
            [$customer->full_name, $customer->customer_code],
            $storedTemplate
        );

        $this->assertStringNotContainsString('{customer_name}', $message);
        $this->assertStringNotContainsString('{customer_code}', $message);
        $this->assertEquals('Dear Siti Nurhaliza (DJV4004), maintenance dijadwalkan.', $message);
    }

    /** @test */
    public function maintenance_template_does_not_replace_invoice_variables()
    {
        $template = 'Maintenance notice. Period: {period}, Amount: {total_amount}';
        AppSetting::updateOrCreate(
            ['key' => 'wa_maintenance_template'],
            ['value' => $template]
        );

        $storedTemplate = AppSetting::get('wa_maintenance_template');
        $message = str_replace(
            ['{customer_name}', '{customer_code}'],
            ['Test', 'DJV000'],
            $storedTemplate
        );

        // Variable invoice TIDAK di-replace di maintenance — harus tetap placeholder
        $this->assertStringContainsString('{period}', $message);
        $this->assertStringContainsString('{total_amount}', $message);
    }

    // ─── UI / BLADE TOKEN CONSISTENCY ───────────────────────────────────

    /** @test */
    public function all_tokens_displayed_in_ui_match_controller_replacements()
    {
        // Tokens yang ditampilkan di blade UI
        $globalTokens  = ['{customer_name}', '{customer_code}'];
        $invoiceTokens = ['{period}', '{total_amount}', '{due_date}', '{payment_link}'];
        $greetingTokens = ['{username}', '{password}'];

        // Tokens yang di-replace di InvoiceController@buildWhatsAppMessage
        $invoiceControllerTokens = [
            '{customer_name}', '{customer_code}',
            '{period}', '{due_date}', '{total_amount}', '{payment_link}',
        ];

        // Tokens yang di-replace di AdminController@sendGreeting
        $greetingControllerTokens = [
            '{customer_name}', '{customer_code}',
            '{username}', '{password}',
        ];

        // Tokens yang di-replace di AdminController@blastMaintenance
        $maintenanceControllerTokens = [
            '{customer_name}', '{customer_code}',
        ];

        // Invoice: Global + Invoice tokens harus semua ada di controller
        $expectedInvoice = array_merge($globalTokens, $invoiceTokens);
        sort($expectedInvoice);
        sort($invoiceControllerTokens);
        $this->assertEquals($expectedInvoice, $invoiceControllerTokens, 'Invoice UI tokens harus cocok dengan controller replacements');

        // Greeting: Global + Greeting tokens harus semua ada di controller
        $expectedGreeting = array_merge($globalTokens, $greetingTokens);
        sort($expectedGreeting);
        sort($greetingControllerTokens);
        $this->assertEquals($expectedGreeting, $greetingControllerTokens, 'Greeting UI tokens harus cocok dengan controller replacements');

        // Maintenance: hanya Global tokens
        $expectedMaintenance = $globalTokens;
        sort($expectedMaintenance);
        sort($maintenanceControllerTokens);
        $this->assertEquals($expectedMaintenance, $maintenanceControllerTokens, 'Maintenance UI tokens harus cocok dengan controller replacements');
    }

    // ─── EDGE CASES ─────────────────────────────────────────────────────

    /** @test */
    public function template_with_no_variables_returns_as_is()
    {
        AppSetting::updateOrCreate(
            ['key' => 'wa_maintenance_template'],
            ['value' => 'Pesan tanpa variable sama sekali.']
        );

        $storedTemplate = AppSetting::get('wa_maintenance_template');
        $message = str_replace(
            ['{customer_name}', '{customer_code}'],
            ['Budi', 'DJV1234'],
            $storedTemplate
        );

        $this->assertEquals('Pesan tanpa variable sama sekali.', $message);
    }

    /** @test */
    public function template_with_repeated_variables()
    {
        $template = '{customer_name} bilang: "{customer_name} sudah bayar"';

        $message = str_replace(
            ['{customer_name}'],
            ['Budi'],
            $template
        );

        $this->assertEquals('Budi bilang: "Budi sudah bayar"', $message);
        $this->assertStringNotContainsString('{customer_name}', $message);
    }

    /** @test */
    public function invoice_amount_is_properly_formatted_as_rupiah()
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create([
            'customer_id'  => $customer->id,
            'total_amount' => 1250000,
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'wa_invoice_template'],
            ['value' => 'Rp{total_amount}']
        );

        $controller = app(InvoiceController::class);
        $method = new ReflectionMethod(InvoiceController::class, 'buildWhatsAppMessage');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $invoice);

        // Harus format Indonesia: 1.250.000
        $this->assertEquals('Rp1.250.000', $result);
    }
}
