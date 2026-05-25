<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeadStage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithJson;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'company_name' => ['required', 'string', 'max:160'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => 'ativo',
            ]);

            $company = Company::create(['name' => $data['company_name'], 'email' => $data['email']]);
            $role = Role::create(['company_id' => $company->id, 'name' => 'dono']);
            Role::create(['company_id' => $company->id, 'name' => 'admin_company']);
            Role::create(['company_id' => $company->id, 'name' => 'agente']);
            $company->users()->attach($user->id, ['role_id' => $role->id]);

            foreach (['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociação', 'Fechado', 'Perdido'] as $position => $name) {
                LeadStage::create(['company_id' => $company->id, 'name' => $name, 'position' => $position + 1]);
            }

            return [$user, $company, $user->createToken('flowcrm')->plainTextToken];
        });

        [$user, $company, $token] = $result;
        $user = [
            ...$user->only(['id', 'name', 'email', 'status']),
            'is_superadmin' => $user->is_superadmin,
            'role' => 'dono',
        ];

        return $this->success(compact('user', 'company', 'token'), 'Conta criada com sucesso.', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Credenciais inválidas.']]);
        }

        $company = $user->companies()->first();
        $token = $user->createToken('flowcrm')->plainTextToken;
        $user = [
            ...$user->only(['id', 'name', 'email', 'status']),
            'is_superadmin' => $user->is_superadmin,
            'role' => $company ? Role::find($company->pivot->role_id)?->name : null,
        ];

        return $this->success(compact('user', 'company', 'token'), 'Login realizado com sucesso.');
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, 'Logout realizado com sucesso.');
    }

    public function me(Request $request)
    {
        return $this->success([
            'user' => $this->serializeUser($request, $request->user()),
            'companies' => $request->user()->companies,
            'company' => $request->attributes->get('current_company'),
        ]);
    }

    private function serializeUser(Request $request, User $user): array
    {
        $company = $request->attributes->get('current_company');
        $role = $company ? $user->companies()->where('companies.id', $company->id)->first()?->pivot?->role_id : null;
        $roleName = $role ? Role::find($role)?->name : null;

        return [
            ...$user->only(['id', 'name', 'email', 'status']),
            'is_superadmin' => $user->is_superadmin,
            'role' => $roleName,
        ];
    }
}
