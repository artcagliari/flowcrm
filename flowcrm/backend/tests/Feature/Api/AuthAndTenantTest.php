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

    public function test_public_register_is_not_available(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Marina Alves',
            'email' => 'marina@example.com',
            'password' => 'password',
        ])->assertNotFound();
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

    public function test_super_admin_can_create_company_with_company_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'active', 'is_superadmin' => true]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/admin/companies', [
            'company' => [
                'name' => 'Empresa Cliente',
                'type' => 'company',
                'profession_mode' => 'empresa',
                'status' => 'active',
                'plan_name' => 'Profissional',
                'max_users' => 5,
            ],
            'admin' => [
                'name' => 'Responsavel Cliente',
                'email' => 'responsavel@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ],
        ])->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('companies', ['name' => 'Empresa Cliente', 'status' => 'active', 'profession_mode' => 'empresa']);
        $this->assertDatabaseHas('users', ['email' => 'responsavel@example.com', 'role' => 'company_admin']);
        $this->assertDatabaseHas('settings', ['setting_key' => 'timezone']);
    }

    public function test_company_user_cannot_access_admin_routes(): void
    {
        [$user] = $this->userWithCompany();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();
    }

    private function userWithCompany(string $email = 'user@example.com'): array
    {
        $user = User::factory()->create(['email' => $email]);
        $company = Company::factory()->create();
        $role = Role::create(['company_id' => $company->id, 'name' => 'company_admin']);
        $company->users()->attach($user->id, ['role_id' => $role->id, 'role' => 'company_admin', 'is_owner' => true, 'status' => 'active']);

        return [$user, $company];
    }
}
