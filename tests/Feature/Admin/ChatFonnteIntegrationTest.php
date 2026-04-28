<?php

namespace Tests\Feature\Admin;

use App\Events\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Mockery\MockInterface;

class ChatFonnteIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['app.payment_domain' => 'localhost']);
        config(['app.staff_domain' => 'localhost']);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function it_can_receive_whatsapp_message_via_webhook()
    {
        Event::fake([ChatMessageCreated::class]);

        $payload = [
            'sender' => '628123456789',
            'message' => 'Halo dari WhatsApp',
        ];

        $response = $this->postJson('/api/whatsapp/fonnte/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('chat_conversations', [
            'source_type' => 'whatsapp',
            'guest_phone' => '628123456789',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'message' => 'Halo dari WhatsApp',
            'sender_type' => 'customer',
        ]);

        Event::assertDispatched(ChatMessageCreated::class);
    }

    /** @test */
    public function it_updates_existing_conversation_on_new_webhook_message()
    {
        $conversation = ChatConversation::create([
            'source_type' => 'whatsapp',
            'guest_phone' => '628123456789',
            'status' => 'open',
            'public_token' => 'test-token',
        ]);

        $payload = [
            'sender' => '628123456789',
            'message' => 'Pesan kedua',
        ];

        $this->postJson('/api/whatsapp/fonnte/webhook', $payload);

        $this->assertEquals(1, ChatConversation::count());
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'message' => 'Pesan kedua',
        ]);
    }

    /** @test */
    public function it_sends_whatsapp_when_admin_replies_to_whatsapp_conversation()
    {
        Event::fake([ChatMessageCreated::class]);

        $conversation = ChatConversation::create([
            'source_type' => 'whatsapp',
            'guest_phone' => '628123456789',
            'status' => 'open',
            'public_token' => 'test-token-2',
        ]);

        $this->mock(WhatsAppService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with('628123456789', 'Balasan Admin')
                ->andReturn(true);
        });

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.messages.store', $conversation), [
                'message' => 'Balasan Admin',
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'message' => 'Balasan Admin',
            'sender_type' => 'admin',
        ]);

        Event::assertDispatched(ChatMessageCreated::class);
    }
}
