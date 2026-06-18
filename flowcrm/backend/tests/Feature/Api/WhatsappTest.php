<?php

namespace Tests\Feature\Api;

use App\Jobs\SendWhatsappMessage;
use App\Models\Client;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsappTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $role = Role::create(['company_id' => $this->company->id, 'name' => 'company_admin']);
        $this->company->users()->attach($this->user->id, ['role_id' => $role->id, 'role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);

        $this->headers = [
            'Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken,
            'X-Company-ID' => (string) $this->company->id,
        ];
    }

    public function test_start_conversation_and_send_message_queues_delivery(): void
    {
        Queue::fake();

        $client = Client::factory()->create(['company_id' => $this->company->id, 'whatsapp' => '(11) 98888-7777']);

        $conversationId = $this->postJson('/api/whatsapp/conversations/start', ['client_id' => $client->id], $this->headers)
            ->assertCreated()
            ->json('data.id');

        $this->assertDatabaseHas('whatsapp_conversations', [
            'id' => $conversationId,
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'phone' => '5511988887777',
        ]);

        $this->postJson("/api/whatsapp/conversations/{$conversationId}/messages", ['body' => 'Ola cliente'], $this->headers)
            ->assertCreated();

        $this->assertDatabaseHas('whatsapp_messages', [
            'conversation_id' => $conversationId,
            'direction' => 'out',
            'body' => 'Ola cliente',
            'status' => 'pending',
        ]);
        Queue::assertPushed(SendWhatsappMessage::class);

        $this->getJson('/api/whatsapp/conversations', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.provider_online', false)
            ->assertJsonCount(1, 'data.conversations.data');
    }

    public function test_inbound_webhook_records_message_and_read_resets_unread(): void
    {
        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", [
            'phone' => '11977776666',
            'body' => 'Oi, tudo bem?',
            'contact_name' => 'Visitante',
        ])->assertOk()->assertJsonPath('data.received', true);

        $this->assertDatabaseHas('whatsapp_conversations', [
            'company_id' => $this->company->id,
            'phone' => '5511977776666',
            'unread_count' => 1,
        ]);
        $this->assertDatabaseHas('whatsapp_messages', ['direction' => 'in', 'body' => 'Oi, tudo bem?', 'status' => 'received']);

        $conversation = \App\Models\WhatsappConversation::where('company_id', $this->company->id)->first();

        $this->patchJson("/api/whatsapp/conversations/{$conversation->id}/read", [], $this->headers)->assertOk();
        $this->assertDatabaseHas('whatsapp_conversations', ['id' => $conversation->id, 'unread_count' => 0]);
    }

    public function test_conversations_are_isolated_by_company(): void
    {
        $otherCompany = Company::factory()->create();
        $conversation = \App\Models\WhatsappConversation::create([
            'company_id' => $otherCompany->id,
            'phone' => '5511955554444',
            'contact_name' => 'Outro',
        ]);

        $this->getJson("/api/whatsapp/conversations/{$conversation->id}/messages", $this->headers)
            ->assertForbidden();
    }
}
