<?php

namespace Tests\Feature\Api;

use App\Jobs\SendWhatsappMessage;
use App\Models\Automation;
use App\Models\Client;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');
        parent::tearDown();
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
        ])->assertOk()->assertJsonPath('status', 'ok');

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

    public function test_inbound_from_unknown_number_creates_lead_and_links_conversation(): void
    {
        Queue::fake();

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", [
            'phone' => '11966665555',
            'body' => 'Quero saber mais sobre o produto',
            'contact_name' => 'Novo Contato',
        ])->assertOk();

        $this->assertDatabaseHas('leads', [
            'company_id' => $this->company->id,
            'name' => 'Novo Contato',
            'whatsapp' => '5511966665555',
            'origin' => 'whatsapp',
        ]);

        $lead = Lead::where('company_id', $this->company->id)->where('whatsapp', '5511966665555')->first();
        $this->assertDatabaseHas('whatsapp_conversations', [
            'company_id' => $this->company->id,
            'phone' => '5511966665555',
            'lead_id' => $lead->id,
        ]);
        $this->assertDatabaseHas('activities', [
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'action' => 'whatsapp_received',
        ]);
    }

    public function test_inbound_from_known_client_links_to_client(): void
    {
        Queue::fake();

        $client = Client::factory()->create(['company_id' => $this->company->id, 'whatsapp' => '11944443333']);

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", [
            'phone' => '11944443333',
            'body' => 'Ola',
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_conversations', [
            'company_id' => $this->company->id,
            'phone' => '5511944443333',
            'client_id' => $client->id,
            'lead_id' => null,
        ]);
    }

    public function test_inbound_message_triggers_automation(): void
    {
        Queue::fake();

        Automation::create([
            'company_id' => $this->company->id,
            'name' => 'Tarefa ao receber WhatsApp',
            'trigger_type' => 'whatsapp.message_received',
            'action_type' => 'create_task',
            'action_config' => ['title' => 'Responder WhatsApp'],
            'is_active' => true,
        ]);

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", [
            'phone' => '11922221111',
            'body' => 'Oi',
            'contact_name' => 'Lead Auto',
        ])->assertOk();

        $this->assertDatabaseHas('tasks', [
            'company_id' => $this->company->id,
            'title' => 'Responder WhatsApp',
        ]);
    }

    public function test_save_settings_and_test_endpoint(): void
    {
        Queue::fake();
        Http::fake();

        $this->putJson('/api/whatsapp/settings', [
            'provider' => 'evolution',
            'is_active' => true,
            'base_url' => 'https://evo.example.com',
            'instance' => 'main',
            'api_key' => 'secret-key',
        ], $this->headers)->assertOk()->assertJsonPath('data.provider', 'evolution');

        // Secret is stored but never returned to the client.
        $this->getJson('/api/whatsapp/settings', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.provider', 'evolution')
            ->assertJsonPath('data.has_api_key', true)
            ->assertJsonMissingPath('data.api_key');

        $this->postJson('/api/whatsapp/test', ['phone' => '11999998888'], $this->headers)
            ->assertOk();
    }

    public function test_meta_webhook_verify_returns_challenge(): void
    {
        config(['services.whatsapp.webhook_token' => 'verify_me']);

        $this->get("/api/webhooks/whatsapp/{$this->company->id}?hub.mode=subscribe&hub.verify_token=verify_me&hub.challenge=12345")
            ->assertOk()
            ->assertContent('12345');
    }

    public function test_meta_webhook_rejects_invalid_signature(): void
    {
        config(['services.whatsapp.meta.app_secret' => 'meta_secret']);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'from' => '5511999998888',
                            'id' => 'wamid.x',
                            'text' => ['body' => 'Oi'],
                        ]],
                    ],
                ]],
            ]],
        ];

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", $payload, [
            'X-Hub-Signature-256' => 'sha256=invalid',
        ])->assertUnauthorized();
    }

    public function test_meta_webhook_accepts_valid_signature(): void
    {
        Queue::fake();
        config(['services.whatsapp.meta.app_secret' => 'meta_secret']);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'contacts' => [['profile' => ['name' => 'Meta User']]],
                        'messages' => [[
                            'from' => '5511888777666',
                            'id' => 'wamid.test',
                            'text' => ['body' => 'Mensagem via Meta'],
                        ]],
                    ],
                ]],
            ]],
        ];

        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, 'meta_secret');

        $this->call(
            'POST',
            "/api/webhooks/whatsapp/{$this->company->id}",
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Hub-Signature-256' => $signature,
            ],
            $body,
        )->assertOk()->assertJsonPath('status', 'ok');

        $this->assertDatabaseHas('whatsapp_messages', ['direction' => 'in', 'body' => 'Mensagem via Meta']);
    }

    public function test_meta_webhook_rejects_unsigned_payload_in_production_env(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['services.whatsapp.meta.app_secret' => 'meta_secret']);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [],
        ];

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", $payload)
            ->assertUnauthorized();
    }

    public function test_meta_webhook_rejects_when_secret_missing_in_production_env(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['services.whatsapp.meta.app_secret' => null]);

        $this->postJson("/api/webhooks/whatsapp/{$this->company->id}", [
            'object' => 'whatsapp_business_account',
            'entry' => [],
        ])->assertStatus(500);
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
