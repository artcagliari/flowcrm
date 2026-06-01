<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithJson;

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Credenciais invalidas.']]);
        }

        if ($user->status !== 'active') {
            return $this->error('Usuario inativo. Entre em contato com o administrador da plataforma.', [], 403);
        }

        $company = $user->isSuperAdmin() ? null : $user->companies()->first();

        if (! $user->isSuperAdmin() && ! $company) {
            return $this->error('Usuario sem empresa vinculada.', [], 403);
        }

        if ($company && $company->status === 'suspended') {
            return $this->error('Empresa suspensa. Entre em contato com o administrador da plataforma.', [], 403);
        }

        if ($company && $company->status !== 'active') {
            return $this->error('Empresa inativa. Entre em contato com o administrador da plataforma.', [], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('crm')->plainTextToken;

        return $this->success([
            'user' => $this->serializeUser($user, $company?->id),
            'company' => $company,
            'token' => $token,
        ], 'Login realizado com sucesso.');
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, 'Logout realizado com sucesso.');
    }

    public function me(Request $request)
    {
        $company = $request->user()->isSuperAdmin()
            ? null
            : ($request->attributes->get('current_company') ?: $request->user()->companies()->first());

        return $this->success([
            'user' => $this->serializeUser($request->user(), $company?->id),
            'companies' => $request->user()->companies,
            'company' => $company,
        ]);
    }

    private function serializeUser(User $user, ?int $companyId): array
    {
        $companyUser = $companyId ? $user->companies()->where('companies.id', $companyId)->first() : null;
        $roleName = $user->isSuperAdmin()
            ? 'super_admin'
            : ($companyUser?->pivot?->role ?: ($companyUser?->pivot?->role_id ? Role::find($companyUser->pivot->role_id)?->name : $user->role));

        return [
            ...$user->only(['id', 'name', 'email', 'avatar', 'phone', 'status', 'last_login_at']),
            'is_superadmin' => $user->isSuperAdmin(),
            'role' => $roleName,
        ];
    }
}
