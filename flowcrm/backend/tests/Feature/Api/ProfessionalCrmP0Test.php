<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\FollowUpSequence;
use App\Models\Lead;
use App\Models\Note;
use App\Models\SequenceEnrollment;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\SequenceRunner;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfessionalCrmP0Test extends TestCase
{
    use RefreshDatabase;

    private function actingAsCompany(User $user, Company $company): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken)
            ->withHeader('X-Company-ID', (string) $company->id);
    }

    public function test_notes_table_has_mentions_after_content_column(): void
    {
        $this->assertTrue(Schema::hasColumn('notes', 'content'));
        $this->assertTrue(Schema::hasColumn('notes', 'mentions'));
    }

    public function test_google_calendar_connect_returns_authorization_url(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
            'services.google.redirect' => 'http://localhost:8000/api/integrations/google-calendar/callback',
        ]);

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user->id, ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);

        $this->actingAsCompany($user, $company)
            ->getJson('/api/integrations/google-calendar/connect')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['url']]);

        $url = $this->actingAsCompany($user, $company)
            ->getJson('/api/integrations/google-calendar/connect')
            ->json('data.url');

        $this->assertStringContainsString('accounts.google.com', $url);
        $this->assertStringContainsString('google-client-id', $url);
    }

    public function test_google_calendar_sync_uses_starts_at_and_google_event_id(): void
    {
        Http::fake(['*' => Http::response(['id' => 'evt-123'], 200)]);

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user->id, ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);

        $appointment = Appointment::create([
            'company_id' => $company->id,
            'title' => 'Consulta',
            'status' => 'agendado',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'notes' => 'Observacao clinica',
        ]);

        \App\Models\CompanyIntegration::create([
            'company_id' => $company->id,
            'provider' => 'google_calendar',
            'is_active' => true,
            'credentials' => ['access_token' => 'token', 'calendar_id' => 'primary'],
        ]);

        $synced = app(GoogleCalendarService::class)->syncAppointments($company->id);

        $this->assertSame(1, $synced);
        $appointment->refresh();
        $this->assertSame('evt-123', $appointment->google_event_id);
        $this->assertSame('Observacao clinica', $appointment->notes);
    }

    public function test_sequence_runner_send_whatsapp_uses_whatsapp_service(): void
    {
        $company = Company::factory()->create();
        $lead = Lead::factory()->create([
            'company_id' => $company->id,
            'whatsapp' => '5511999999999',
            'name' => 'Maria',
        ]);

        $sequence = FollowUpSequence::create([
            'company_id' => $company->id,
            'name' => 'Teste',
            'trigger_type' => 'lead_created',
            'is_active' => true,
        ]);

        $sequence->steps()->create([
            'position' => 0,
            'delay_days' => 0,
            'action_type' => 'send_whatsapp',
            'action_config' => ['body' => 'Ola {nome}'],
        ]);

        $enrollment = SequenceEnrollment::create([
            'sequence_id' => $sequence->id,
            'lead_id' => $lead->id,
            'current_step' => 0,
            'status' => 'active',
            'next_run_at' => now()->subMinute(),
        ]);

        app(SequenceRunner::class)->processDue();

        $this->assertDatabaseHas('whatsapp_conversations', [
            'company_id' => $company->id,
            'lead_id' => $lead->id,
        ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'direction' => 'out',
            'body' => 'Ola Maria',
        ]);
    }

    public function test_lead_convert_creates_client(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['profession_mode' => 'empresa']);
        $company->users()->attach($user->id, ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);
        $lead = Lead::factory()->create(['company_id' => $company->id, 'status' => 'novo']);

        $this->actingAsCompany($user, $company)
            ->postJson("/api/leads/{$lead->id}/convert")
            ->assertOk()
            ->assertJsonPath('data.client.name', $lead->name);

        $client = \App\Models\Client::where('company_id', $company->id)->first();
        $this->assertNotNull($client);
        $this->assertDatabaseMissing('professional_cases', [
            'client_id' => $client->id,
            'lead_id' => $lead->id,
        ]);
    }

    public function test_client_lgpd_export_and_anonymize(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user->id, ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);
        $client = \App\Models\Client::factory()->create(['company_id' => $company->id, 'name' => 'Cliente Teste']);
        Note::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Dado sensivel de saude',
            'sensitivity_level' => 'sensitive',
            'type' => 'clinico',
        ]);

        $this->actingAsCompany($user, $company)
            ->getJson("/api/clients/{$client->id}/export-data")
            ->assertOk()
            ->assertJsonPath('client.name', 'Cliente Teste');

        $this->actingAsCompany($user, $company)
            ->postJson("/api/clients/{$client->id}/anonymize")
            ->assertOk();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Titular anonimizado']);
    }

    public function test_whatsapp_bot_registers_lead_and_offers_menu(): void
    {
        $company = Company::factory()->create();

        app(WhatsappService::class)->receiveMessage($company->id, [
            'phone' => '5511888777666',
            'body' => 'ola',
            'contact_name' => 'Joao',
        ]);

        $this->assertDatabaseHas('leads', [
            'company_id' => $company->id,
            'origin' => 'whatsapp',
        ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'direction' => 'out',
        ]);
    }
}
