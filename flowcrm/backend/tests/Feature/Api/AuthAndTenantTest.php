<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_company_and_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Marina Alves',
            'email' => 'marina@example.com',
            'password' => 'password',
            'company_name' => 'Flow Demo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'company', 'token']]);

        $this->assertDatabaseHas('companies', ['name' => 'Flow Demo']);
        $this->assertDatabaseHas('lead_stages', ['name' => 'Novo lead']);
    }

    public function test_current_company_header_blocks_cross_company_records(): void
    {
        [$user, $companyA] = $this->userWithCompany();
        [, $companyB] = $this->userWithCompany('other@example.com');
        $clientB = Client::factory()->create(['company_id' => $companyB->id]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Company-ID', $companyA->id)
            ->getJson("/api/clients/{$clientB->id}")
            ->assertForbidden();
    }

    private function userWithCompany(string $email = 'user@example.com'): array
    {
        $user = User::factory()->create(['email' => $email]);
        $company = Company::factory()->create();
        $role = Role::create(['company_id' => $company->id, 'name' => 'dono']);
        $company->users()->attach($user->id, ['role_id' => $role->id]);

        return [$user, $company];
    }
}
