<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\MockInterface;

class InvoiceBulkWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['app.payment_domain' => 'localhost']);

        // Create Admin
        $this->admin = new User();
        $this->admin->name = 'Admin';
        $this->admin->email = 'admin@test.com';
        $this->admin->password = bcrypt('password');
        $this->admin->role = 'admin';
        $this->admin->save();
    }

    /** @test */
    public function it_can_send_bulk_whatsapp_messages_to_unpaid_invoices()
    {
        // 1. Setup Data
        $package = new Package();
        $package->name = 'Test Pack';
        $package->profile = 'TEST-PROFILE';
        $package->monthly_price = 200000;
        $package->is_active = true;
        $package->save();

        $customer = new Customer();
        $customer->package_id = $package->id;
        $customer->customer_code = 'CUST001';
        $customer->full_name = 'Test Customer';
        $customer->whatsapp_number = '6282246468335';
        $customer->address = 'Test Address';
        $customer->active_since = now();
        $customer->username = 'testuser';
        $customer->password = bcrypt('pass');
        $customer->status = 'active';
        $customer->save();

        $invoice = new Invoice();
        $invoice->customer_id = $customer->id;
        $invoice->invoice_number = 'INV-TEST-01';
        $invoice->period_start = now()->today();
        $invoice->period_end = now()->addMonth();
        $invoice->amount = 200000;
        $invoice->total_amount = 200000;
        $invoice->due_date = now()->addDays(10);
        $invoice->status = 'unpaid';
        $invoice->save();

        // 2. Mock WhatsAppService
        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->andReturn(true);
        });

        // 3. Action
        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.bulk_whatsapp'));

        // 4. Assert
        $response->assertStatus(302);
        $this->assertEquals(1, Invoice::where('status', 'unpaid')->count());
    }

    /** @test */
    public function it_skips_and_reports_invalid_whatsapp_numbers()
    {
        $package = new Package();
        $package->name = 'Test Pack 2';
        $package->profile = 'TEST-PROFILE-2';
        $package->monthly_price = 100000;
        $package->save();

        // 1. Success case
        $c1 = new Customer();
        $c1->package_id = $package->id; $c1->customer_code = 'C1'; $c1->full_name = 'Success'; $c1->whatsapp_number = '6281'; $c1->address = 'A'; $c1->active_since = now(); $c1->username = 'u1'; $c1->password = 'p'; $c1->save();

        // 2. Empty number case
        $c2 = new Customer();
        $c2->package_id = $package->id; $c2->customer_code = 'C2'; $c2->full_name = 'Empty'; $c2->whatsapp_number = ''; $c2->address = 'A'; $c2->active_since = now(); $c2->username = 'u2'; $c2->password = 'p'; $c2->save();

        // 3. Invalid number case (Service returns false)
        $c3 = new Customer();
        $c3->package_id = $package->id; $c3->customer_code = 'C3'; $c3->full_name = 'Invalid'; $c3->whatsapp_number = '6283'; $c3->address = 'A'; $c3->active_since = now(); $c3->username = 'u3'; $c3->password = 'p'; $c3->save();

        Invoice::create(['customer_id' => $c1->id, 'invoice_number' => 'I1', 'status' => 'unpaid', 'amount' => 100, 'total_amount' => 100, 'period_start' => now(), 'period_end' => now(), 'due_date' => now()]);
        Invoice::create(['customer_id' => $c2->id, 'invoice_number' => 'I2', 'status' => 'unpaid', 'amount' => 100, 'total_amount' => 100, 'period_start' => now(), 'period_end' => now(), 'due_date' => now()]);
        Invoice::create(['customer_id' => $c3->id, 'invoice_number' => 'I3', 'status' => 'unpaid', 'amount' => 100, 'total_amount' => 100, 'period_start' => now(), 'period_end' => now(), 'due_date' => now()]);

        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            // First call success, second call (for C3) fails
            $mock->shouldReceive('sendMessage')->with('6281', \Mockery::any())->once()->andReturn(true);
            $mock->shouldReceive('sendMessage')->with('6283', \Mockery::any())->once()->andReturn(false);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.invoices.bulk_whatsapp'));
        
        $response->assertSessionHas('success', 'Berhasil mengirim 1 pesan WhatsApp. (1 nomor kosong, 1 nomor tidak terhubung WA diloncati).');
    }
}
