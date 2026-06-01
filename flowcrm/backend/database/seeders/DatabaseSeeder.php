<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadStage;
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
        $superAdmin = User::updateOrCreate(
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
                'features' => ['clientes', 'leads', 'agenda', 'financeiro', 'documentos'],
            ]
        );

        $company = Company::updateOrCreate(
            ['email' => 'demo@flowcrm.com'],
            [
                'name' => 'FlowCRM Demo',
                'type' => 'company',
                'owner_name' => 'Ana Souza',
                'phone' => '11999990000',
                'whatsapp' => '11999990000',
                'city' => 'Sao Paulo',
                'state' => 'SP',
                'primary_color' => '#4F8CFF',
                'status' => 'active',
                'plan_name' => $plan->name,
                'max_users' => $plan->max_users,
                'starts_at' => now()->toDateString(),
                'expires_at' => now()->addMonth()->toDateString(),
            ]
        );

        $companyAdmin = User::updateOrCreate(
            ['email' => 'empresa@crm.com'],
            [
                'name' => 'Ana Souza',
                'password' => Hash::make('password'),
                'phone' => '11999990000',
                'role' => 'company_admin',
                'status' => 'active',
                'is_superadmin' => false,
            ]
        );

        $company->users()->syncWithoutDetaching([
            $companyAdmin->id => ['role' => 'company_admin', 'is_owner' => true, 'status' => 'active'],
        ]);

        Subscription::updateOrCreate(
            ['company_id' => $company->id, 'plan_id' => $plan->id],
            ['status' => 'trial', 'starts_at' => now()->toDateString(), 'ends_at' => now()->addMonth()->toDateString()]
        );

        $stages = collect(['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociacao'])->map(
            fn ($name, $position) => LeadStage::updateOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                ['position' => $position]
            )
        );

        $client = Client::updateOrCreate(
            ['company_id' => $company->id, 'email' => 'cliente@exemplo.com'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'name' => 'Cliente Exemplo',
                'phone' => '11988887777',
                'whatsapp' => '11988887777',
                'city' => 'Sao Paulo',
                'origin' => 'Indicacao',
                'status' => 'ativo',
                'notes' => 'Cliente ativo criado pelo seeder demo.',
                'last_contact_at' => now()->subDay(),
            ]
        );

        $lead = Lead::updateOrCreate(
            ['company_id' => $company->id, 'email' => 'lead@exemplo.com'],
            [
                'owner_id' => $companyAdmin->id,
                'lead_stage_id' => $stages[2]->id,
                'name' => 'Lead Exemplo',
                'phone' => '11977776666',
                'whatsapp' => '11977776666',
                'origin' => 'Instagram',
                'interest' => 'Plano Pro',
                'temperature' => 'quente',
                'status' => 'qualificado',
                'estimated_value' => 2500,
                'notes' => 'Lead qualificado aguardando proposta.',
                'last_interaction_at' => now()->subHours(6),
                'next_action_at' => now()->addDay(),
            ]
        );

        Task::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Enviar proposta para lead exemplo'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'lead_id' => $lead->id,
                'description' => 'Preparar proposta comercial e enviar por WhatsApp.',
                'due_date' => now()->addDay()->toDateString(),
                'due_at' => now()->addDay(),
                'priority' => 'alta',
                'status' => 'pendente',
            ]
        );

        Appointment::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Reuniao de alinhamento com cliente exemplo'],
            [
                'user_id' => $companyAdmin->id,
                'owner_id' => $companyAdmin->id,
                'client_id' => $client->id,
                'description' => 'Revisar proximos passos do projeto.',
                'type' => 'reuniao',
                'status' => 'agendado',
                'start_at' => now()->addDays(2)->setTime(10, 0),
                'starts_at' => now()->addDays(2)->setTime(10, 0),
                'end_at' => now()->addDays(2)->setTime(11, 0),
                'ends_at' => now()->addDays(2)->setTime(11, 0),
                'location' => 'Google Meet',
            ]
        );

        Payment::updateOrCreate(
            ['company_id' => $company->id, 'description' => 'Mensalidade cliente exemplo'],
            [
                'client_id' => $client->id,
                'user_id' => $companyAdmin->id,
                'amount' => 149.90,
                'category' => 'mensalidade',
                'due_date' => now()->addDays(7)->toDateString(),
                'payment_method' => 'Pix',
                'status' => 'pendente',
            ]
        );

        Expense::updateOrCreate(
            ['company_id' => $company->id, 'description' => 'Ferramentas comerciais'],
            [
                'user_id' => $companyAdmin->id,
                'amount' => 49.90,
                'category' => 'software',
                'due_date' => now()->toDateString(),
                'paid_at' => now()->toDateString(),
                'payment_method' => 'cartao',
                'status' => 'pago',
            ]
        );

        Activity::updateOrCreate(
            ['company_id' => $company->id, 'client_id' => $client->id, 'action' => 'seed_demo'],
            [
                'user_id' => $companyAdmin->id,
                'subject_type' => Client::class,
                'subject_id' => $client->id,
                'description' => 'Ambiente demo criado com cliente, lead, tarefa, agenda e financeiro conectados.',
            ]
        );
    }
}
