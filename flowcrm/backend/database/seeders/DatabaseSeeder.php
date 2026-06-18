quero <?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name' => 'Admin Master',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'is_superadmin' => true,
            ]
        );

        $plan = Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'monthly_price' => 149.90,
                'max_users' => 10,
                'features' => ['clientes', 'leads', 'oportunidades', 'agenda', 'financeiro', 'documentos'],
            ]
        );

        $this->seedDemoCompany($plan);
    }

    private function seedDemoCompany(Plan $plan): void
    {
        $company = Company::updateOrCreate(
            ['email' => 'demo@flowcrm.com'],
            [
                'name' => 'Empresa Demo Comercial',
                'legal_name' => 'Empresa Demo Comercial LTDA',
                'document' => '12.345.678/0001-90',
                'phone' => '1133334444',
                'whatsapp' => '11999998888',
                'city' => 'Sao Paulo',
                'state' => 'SP',
                'type' => 'company',
                'profession_mode' => 'empresa',
                'status' => 'active',
                'plan_name' => $plan->name,
                'max_users' => $plan->max_users,
                'primary_color' => '#4F8CFF',
            ]
        );

        $companyAdmin = User::updateOrCreate(
            ['email' => 'empresa@crm.com'],
            [
                'name' => 'Ana Comercial',
                'password' => Hash::make('password'),
                'role' => 'company_admin',
                'status' => 'active',
            ]
        );

        $company->users()->syncWithoutDetaching([
            $companyAdmin->id => ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active'],
        ]);

        Subscription::updateOrCreate(
            ['company_id' => $company->id, 'plan_id' => $plan->id],
            ['status' => 'trial', 'starts_at' => now()->toDateString(), 'ends_at' => now()->addMonth()->toDateString()]
        );

        $pipeline = Pipeline::updateOrCreate(
            ['company_id' => $company->id, 'is_default' => true],
            ['name' => 'Funil comercial']
        );

        foreach (['Novo lead', 'Qualificacao', 'Proposta enviada', 'Negociacao', 'Fechado ganho', 'Fechado perdido'] as $position => $name) {
            LeadStage::updateOrCreate(
                ['company_id' => $company->id, 'pipeline_id' => $pipeline->id, 'name' => $name],
                ['position' => $position, 'color' => '#4F8CFF']
            );
        }

        $client = Client::updateOrCreate(
            ['company_id' => $company->id, 'email' => 'cliente@exemplo.com'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'name' => 'Cliente Exemplo LTDA',
                'phone' => '11988887777',
                'whatsapp' => '11988887777',
                'city' => 'Sao Paulo',
                'origin' => 'Indicacao',
                'status' => 'em_atendimento',
                'notes' => 'Cliente ativo em negociacao de contrato anual.',
                'last_contact_at' => now()->subDay(),
            ]
        );

        $lead = Lead::updateOrCreate(
            ['company_id' => $company->id, 'email' => 'lead@exemplo.com'],
            [
                'owner_id' => $companyAdmin->id,
                'name' => 'Lead Exemplo',
                'phone' => '11977776666',
                'whatsapp' => '11977776666',
                'origin' => 'WhatsApp',
                'interest' => 'Interesse no plano corporativo',
                'status' => 'novo',
                'notes' => 'Lead aguardando retorno comercial.',
                'last_interaction_at' => now()->subHours(6),
                'next_action_at' => now()->addDay(),
            ]
        );

        Task::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Retornar lead — Empresa Demo Comercial'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'lead_id' => $lead->id,
                'description' => 'Ligar para qualificar necessidade e enviar proposta.',
                'due_date' => now()->addDay()->toDateString(),
                'due_at' => now()->addDay(),
                'priority' => 'alta',
                'status' => 'pendente',
            ]
        );

        Appointment::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Reuniao comercial'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'client_id' => $client->id,
                'description' => 'Apresentacao da proposta comercial.',
                'type' => 'reuniao',
                'status' => 'agendado',
                'starts_at' => now()->addDays(2)->setTime(10, 0),
                'ends_at' => now()->addDays(2)->setTime(11, 0),
                'location' => 'Sala de reunioes',
            ]
        );

        Payment::updateOrCreate(
            ['company_id' => $company->id, 'description' => 'Contrato anual — Cliente Exemplo LTDA'],
            [
                'client_id' => $client->id,
                'user_id' => $companyAdmin->id,
                'amount' => 1500.00,
                'category' => 'servicos',
                'due_date' => now()->addDays(7)->toDateString(),
                'payment_method' => 'Pix',
                'status' => 'pendente',
            ]
        );

        Expense::updateOrCreate(
            ['company_id' => $company->id, 'description' => 'Assinatura de software'],
            [
                'user_id' => $companyAdmin->id,
                'amount' => 149.90,
                'category' => 'software',
                'due_date' => now()->toDateString(),
                'paid_at' => now()->toDateString(),
                'payment_method' => 'cartao',
                'status' => 'pago',
            ]
        );

        Activity::updateOrCreate(
            ['company_id' => $company->id, 'client_id' => $client->id, 'action' => 'seed_demo_empresa'],
            [
                'user_id' => $companyAdmin->id,
                'subject_type' => Client::class,
                'subject_id' => $client->id,
                'description' => 'Ambiente demo empresa criado com leads, clientes e agenda.',
            ]
        );
    }
}
