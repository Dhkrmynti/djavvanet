<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class WhatsAppValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    /** @test */
    public function it_returns_valid_true_for_registered_number()
    {
        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            $mock->shouldReceive('formatNumber')->andReturnUsing(fn($num) => $num);
            $mock->shouldReceive('validateNumber')->with('628123456789')->once()->andReturn(true);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.customers.check_whatsapp'), [
            'number' => '628123456789'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'message' => 'Terhubung ke WhatsApp'
        ]);
    }

    /** @test */
    public function it_returns_valid_false_for_unregistered_number()
    {
        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            $mock->shouldReceive('formatNumber')->andReturnUsing(fn($num) => $num);
            $mock->shouldReceive('validateNumber')->with('628000000000')->once()->andReturn(false);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.customers.check_whatsapp'), [
            'number' => '628000000000'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Tidak terhubung ke WhatsApp'
        ]);
    }

    /** @test */
    public function it_rejects_too_short_numbers()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.customers.check_whatsapp'), [
            'number' => '123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Nomor terlalu pendek'
        ]);
    }
}
