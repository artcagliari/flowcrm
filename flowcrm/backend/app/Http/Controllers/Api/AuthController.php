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
            $company->users()->attach($user->id, ['role_id' => $role->id]);

            foreach (['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociação', 'Fechado', 'Perdido'] as $position => $name) {
                LeadStage::create(['company_id' => $company->id, 'name' => $name, 'position' => $position + 1]);
            }

            return [$user, $company, $user->createToken('flowcrm')->plainTextToken];
        });

        [$user, $company, $token] = $result;

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
            'user' => $request->user(),
            'companies' => $request->user()->companies,
            'company' => $request->attributes->get('current_company'),
        ]);
    }
}
