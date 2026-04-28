<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WhatsAppBlastTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    /** @test */
    public function it_can_save_multiple_whatsapp_templates()
    {
        $response = $this->post(route('admin.settings.whatsapp_template.save'), [
            'invoice_template' => 'Invoice {customer_name}',
            'greeting_template' => 'Greeting {username}',
            'maintenance_template' => 'Maintenance info',
        ]);

        $response->assertStatus(302);
        $this->assertEquals('Invoice {customer_name}', AppSetting::get('wa_invoice_template'));
        $this->assertEquals('Greeting {username}', AppSetting::get('wa_greeting_template'));
        $this->assertEquals('Maintenance info', AppSetting::get('wa_maintenance_template'));
    }

    /** @test */
    public function it_can_upload_maintenance_image()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('maintenance.jpg');

        $response = $this->post(route('admin.settings.whatsapp_template.save'), [
            'maintenance_image_file' => $file,
        ]);

        $response->assertStatus(302);
        
        $imageUrl = AppSetting::get('wa_maintenance_image');
        $this->assertNotEmpty($imageUrl);
        $this->assertStringContainsString('maintenance_', $imageUrl);
    }

    /** @test */
    public function it_can_send_manual_greeting()
    {
        $customer = Customer::factory()->create([
            'full_name' => 'John Doe',
            'whatsapp_number' => '08123456789',
            'username' => 'johndoe',
            'password_plain' => 'secret123'
        ]);

        AppSetting::updateOrCreate(['key' => 'wa_greeting_template'], ['value' => 'Hello {customer_name}, your user is {username}']);

        $this->mock(WhatsAppService::class, function ($mock) use ($customer) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with($customer->whatsapp_number, 'Hello John Doe, your user is johndoe')
                ->andReturn(true);
        });

        $response = $this->post(route('admin.customers.greeting', $customer->id));

        $response->assertJson(['ok' => true]);
    }

    /** @test */
    public function it_can_blast_maintenance_to_all_active_customers()
    {
        Customer::factory()->count(3)->create(['status' => 'active']);
        Customer::factory()->create(['status' => 'inactive']);

        AppSetting::updateOrCreate(['key' => 'wa_maintenance_template'], ['value' => 'Maintenance {customer_name}']);
        AppSetting::updateOrCreate(['key' => 'wa_maintenance_image'], ['value' => 'http://example.com/img.jpg']);

        $this->mock(WhatsAppService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->times(3)
                ->withAnyArgs()
                ->andReturn(true);
        });

        $response = $this->post(route('admin.customers.blast_maintenance'), [
            'target' => 'all'
        ]);

        $response->assertJson(['ok' => true]);
        $response->assertJsonFragment(['message' => 'Blast selesai. Berhasil: 3, Gagal: 0']);
    }
}
