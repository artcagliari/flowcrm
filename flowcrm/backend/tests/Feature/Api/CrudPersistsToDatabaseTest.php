<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrudPersistsToDatabaseTest extends TestCase
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

    public function test_client_create_update_delete_persists_to_database(): void
    {
        $id = $this->postJson('/api/clients', [
            'name' => 'Cliente Novo',
            'email' => 'cliente@example.com',
            'status' => 'active',
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('clients', ['id' => $id, 'company_id' => $this->company->id, 'name' => 'Cliente Novo']);

        $this->putJson("/api/clients/{$id}", [
            'name' => 'Cliente Editado',
            'email' => 'cliente@example.com',
            'status' => 'em atendimento',
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('clients', ['id' => $id, 'name' => 'Cliente Editado', 'status' => 'em atendimento']);

        $this->deleteJson("/api/clients/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Client::class, ['id' => $id]);
    }

    public function test_task_create_update_delete_persists_to_database(): void
    {
        $id = $this->postJson('/api/tasks', [
            'title' => 'Tarefa Nova',
            'priority' => 'alta',
            'status' => 'pendente',
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('tasks', ['id' => $id, 'title' => 'Tarefa Nova']);

        $this->putJson("/api/tasks/{$id}", [
            'title' => 'Tarefa Editada',
            'priority' => 'urgente',
            'status' => 'em andamento',
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $id, 'title' => 'Tarefa Editada', 'status' => 'em andamento']);

        $this->deleteJson("/api/tasks/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Task::class, ['id' => $id]);
    }

    public function test_appointment_create_update_delete_persists_to_database(): void
    {
        $id = $this->postJson('/api/appointments', [
            'title' => 'Reuniao Nova',
            'type' => 'reuniao',
            'status' => 'agendado',
            'starts_at' => now()->addDay()->toISOString(),
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('appointments', ['id' => $id, 'title' => 'Reuniao Nova']);

        $this->putJson("/api/appointments/{$id}", [
            'title' => 'Reuniao Editada',
            'type' => 'retorno',
            'status' => 'confirmado',
            'starts_at' => now()->addDays(2)->toISOString(),
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'title' => 'Reuniao Editada', 'status' => 'confirmado']);

        $this->deleteJson("/api/appointments/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Appointment::class, ['id' => $id]);
    }

    public function test_payment_create_update_delete_persists_to_database(): void
    {
        $id = $this->postJson('/api/payments', [
            'description' => 'Pagamento Novo',
            'amount' => 1500,
            'status' => 'pendente',
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('payments', ['id' => $id, 'description' => 'Pagamento Novo']);

        $this->putJson("/api/payments/{$id}", [
            'description' => 'Pagamento Editado',
            'amount' => 1900,
            'status' => 'pago',
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $id, 'description' => 'Pagamento Editado', 'status' => 'pago']);

        $this->deleteJson("/api/payments/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Payment::class, ['id' => $id]);
    }

    public function test_expense_create_update_delete_persists_to_database(): void
    {
        $id = $this->postJson('/api/expenses', [
            'description' => 'Despesa Nova',
            'amount' => 400,
            'status' => 'pendente',
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('expenses', ['id' => $id, 'description' => 'Despesa Nova']);

        $this->putJson("/api/expenses/{$id}", [
            'description' => 'Despesa Editada',
            'amount' => 500,
            'status' => 'pago',
        ], $this->headers)->assertOk();

        $this->assertDatabaseHas('expenses', ['id' => $id, 'description' => 'Despesa Editada', 'status' => 'pago']);

        $this->deleteJson("/api/expenses/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Expense::class, ['id' => $id]);
    }

    public function test_document_upload_filter_download_and_delete_persists_to_database(): void
    {
        Storage::fake();

        $client = Client::factory()->create(['company_id' => $this->company->id]);

        $id = $this->postJson('/api/documents', [
            'file' => UploadedFile::fake()->create('contrato-flowcrm.pdf', 200, 'application/pdf'),
            'category' => 'contrato',
            'client_id' => $client->id,
        ], $this->headers)->assertCreated()->json('data.id');

        $document = Document::findOrFail($id);

        $this->assertDatabaseHas('documents', [
            'id' => $id,
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'name' => 'contrato-flowcrm.pdf',
            'category' => 'contrato',
        ]);
        Storage::assertExists($document->path);

        $this->getJson('/api/documents?search=flowcrm&category=contrato', $this->headers)
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'contrato-flowcrm.pdf');

        $this->getJson("/api/documents/{$id}/download", $this->headers)->assertOk();

        $this->deleteJson("/api/documents/{$id}", [], $this->headers)->assertOk();
        $this->assertSoftDeleted(Document::class, ['id' => $id]);
    }

    public function test_index_supports_search_filters_and_sorting(): void
    {
        Client::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Alpha Cliente',
            'status' => 'ativo',
            'origin' => 'Google',
            'city' => 'Sao Paulo',
        ]);

        Client::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Beta Cliente',
            'status' => 'arquivado',
            'origin' => 'Instagram',
            'city' => 'Rio',
        ]);

        $this->getJson('/api/clients?search=Alpha', $this->headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alpha Cliente');

        $this->getJson('/api/clients?status=arquivado', $this->headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Beta Cliente');

        $this->getJson('/api/clients?sort_by=name&sort_dir=asc', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha Cliente');
    }

    public function test_dashboard_is_grouped_by_crm_areas(): void
    {
        Client::factory()->create(['company_id' => $this->company->id, 'status' => 'ativo', 'origin' => 'Google', 'city' => 'Sao Paulo']);
        Task::create(['company_id' => $this->company->id, 'title' => 'Tarefa relatorio', 'status' => 'pendente', 'priority' => 'alta']);
        Appointment::create(['company_id' => $this->company->id, 'title' => 'Reuniao relatorio', 'status' => 'agendado', 'type' => 'reuniao', 'starts_at' => now()->addDay()]);
        Payment::create(['company_id' => $this->company->id, 'description' => 'Receita relatorio', 'status' => 'pago', 'amount' => 1200, 'paid_at' => now()->toDateString()]);
        Expense::create(['company_id' => $this->company->id, 'description' => 'Gasto relatorio', 'status' => 'pago', 'category' => 'Marketing', 'amount' => 300, 'paid_at' => now()->toDateString()]);

        $this->getJson('/api/dashboard', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.stats.clients', 1)
            ->assertJsonPath('data.stats.today_appointments', 0)
            ->assertJsonPath('data.stats.monthly_revenue', 1200)
            ->assertJsonPath('data.stats.monthly_expenses', 300)
            ->assertJsonPath('data.stats.estimated_profit', 900);
    }

    public function test_owner_can_manage_company_users_and_user_can_update_own_profile(): void
    {
        $id = $this->postJson('/api/users', [
            'name' => 'Agente Flow',
            'email' => 'agente@flowcrm.test',
            'password' => 'password123',
            'status' => 'ativo',
            'role' => 'employee',
        ], $this->headers)->assertCreated()->json('data.id');

        $this->assertDatabaseHas('users', ['id' => $id, 'email' => 'agente@flowcrm.test', 'status' => 'active']);
        $this->assertDatabaseHas('company_user', ['company_id' => $this->company->id, 'user_id' => $id]);

        $this->putJson("/api/users/{$id}", [
            'name' => 'Admin Company',
            'email' => 'admin.company@flowcrm.test',
            'status' => 'active',
            'role' => 'company_admin',
        ], $this->headers)->assertOk()->assertJsonPath('data.role', 'company_admin');

        $agent = User::findOrFail($id);
        $agentHeaders = [
            'Authorization' => 'Bearer '.$agent->createToken('test')->plainTextToken,
            'X-Company-ID' => (string) $this->company->id,
        ];

        $this->putJson('/api/profile', [
            'name' => 'Meu Perfil Editado',
            'email' => 'perfil.editado@flowcrm.test',
        ], $agentHeaders)->assertOk()->assertJsonPath('data.name', 'Meu Perfil Editado');

        $this->deleteJson("/api/users/{$id}", [], $this->headers)->assertOk();
        $this->assertDatabaseMissing('company_user', ['company_id' => $this->company->id, 'user_id' => $id]);
    }
}
