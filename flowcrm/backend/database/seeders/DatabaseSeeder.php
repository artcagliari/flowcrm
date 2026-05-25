<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@flowcrm.test',
            'password' => 'password',
            'status' => 'ativo',
        ]);

        $company = Company::create(['name' => 'FlowCRM Demo', 'email' => 'admin@flowcrm.test']);
        $role = Role::create(['company_id' => $company->id, 'name' => 'dono']);
        Role::create(['company_id' => $company->id, 'name' => 'admin_company']);
        Role::create(['company_id' => $company->id, 'name' => 'agente']);
        $company->users()->attach($user->id, ['role_id' => $role->id]);

        foreach (['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociação', 'Fechado', 'Perdido'] as $position => $name) {
            LeadStage::create(['company_id' => $company->id, 'name' => $name, 'position' => $position + 1]);
        }

        Plan::insert([
            ['name' => 'Individual', 'slug' => 'individual', 'monthly_price' => 79, 'max_users' => 1, 'features' => json_encode(['clientes', 'leads']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipe', 'slug' => 'equipe', 'monthly_price' => 249, 'max_users' => 5, 'features' => json_encode(['funil', 'documentos', 'relatorios']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Profissional', 'slug' => 'profissional', 'monthly_price' => 499, 'max_users' => null, 'features' => json_encode(['automacoes', 'integracoes']), 'created_at' => now(), 'updated_at' => now()],
        ]);

        Client::factory(8)->create(['company_id' => $company->id, 'owner_id' => $user->id]);
        $stages = LeadStage::where('company_id', $company->id)->pluck('id')->all();
        Lead::factory(14)->create(fn () => ['company_id' => $company->id, 'owner_id' => $user->id, 'lead_stage_id' => fake()->randomElement($stages)]);
        Task::factory(10)->create(['company_id' => $company->id, 'owner_id' => $user->id]);
        Payment::factory(6)->create(['company_id' => $company->id]);
        Expense::factory(4)->create(['company_id' => $company->id]);
    }
}
