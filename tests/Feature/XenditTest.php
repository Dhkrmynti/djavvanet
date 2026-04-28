<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\XenditSetting;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        
        XenditSetting::create([
            'is_active' => true,
            'api_key' => 'xnd_test_key',
            'callback_token' => 'token_test',
            'admin_fee' => 5000
        ]);
    }

    public function test_xendit_invoice_includes_admin_fee()
    {
        $invoice = Invoice::factory()->create([
            'amount' => 100000,
            'total_amount' => 100000
        ]);
        
        // We test the logic inside XenditService by calling it
        // Or we can test the portal payment route that triggers it
        $service = new XenditService();
        
        // This is a bit tricky since it calls external API. 
        // Ideally we mock the Http facade.
        \Illuminate\Support\Facades\Http::fake([
            'https://api.xendit.co/v2/invoices' => \Illuminate\Support\Facades\Http::response(['invoice_url' => 'http://test.com'], 200)
        ]);

        $result = $service->createInvoice($invoice);

        $this->assertEquals('http://test.com', $result['invoice_url']);

        // Verify the payload sent to Xendit
        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            $data = $request->data();
            $items = collect($data['items']);
            
            return $data['amount'] == 105000 && 
                   $items->contains('name', 'Biaya Admin');
        });
    }
}
