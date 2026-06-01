<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use RespondsWithJson;

    private array $companyRoles = ['company_admin', 'employee', 'financial', 'viewer'];

    public function index(Request $request)
    {
        $company = $request->attributes->get('current_company');

        return $this->success([
            'users' => $company->users()
                ->withPivot('role_id')
                ->get()
                ->map(fn (User $user) => $this->serializeUser($user, $company->id)),
            'roles' => $this->availableRoles($request),
            'can_manage_users' => $this->canManageUsers($request),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageUsers($request), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', Rule::in(['active', 'inactive', 'ativo', 'inativo'])],
            'role' => ['required', Rule::in($this->availableRoles($request))],
        ]);
        $data['status'] = $data['status'] === 'ativo' ? 'active' : ($data['status'] === 'inativo' ? 'inactive' : $data['status']);

        $company = $request->attributes->get('current_company');
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => $data['status'],
            'role' => $data['role'],
            'is_superadmin' => false,
        ]);

        $role = $this->roleForCompany($company->id, $data['role']);
        $company->users()->attach($user->id, ['role_id' => $role->id, 'role' => $data['role'], 'is_owner' => $data['role'] === 'company_admin', 'status' => $data['status']]);

        return $this->success($this->serializeUser($user, $company->id), 'Usuario criado com sucesso.', 201);
    }

    public function update(Request $request, User $user)
    {
        $isSelf = $request->user()->id === $user->id;
        abort_unless($isSelf || $this->canManageUsers($request), 403);
        abort_if(! $isSelf && ! $this->userBelongsToCurrentCompany($request, $user) && ! $request->user()->is_superadmin, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'ativo', 'inativo'])],
            'role' => ['sometimes', Rule::in($this->availableRoles($request))],
        ]);
        if (isset($data['status'])) {
            $data['status'] = $data['status'] === 'ativo' ? 'active' : ($data['status'] === 'inativo' ? 'inactive' : $data['status']);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($this->canManageUsers($request)) {
            if (isset($data['status'])) {
                $user->status = $data['status'];
            }

            if (isset($data['role'])) {
                $user->role = $data['role'];
                $user->is_superadmin = false;
                $role = $this->roleForCompany($request->attributes->get('current_company')->id, $data['role']);
                $request->attributes->get('current_company')->users()->syncWithoutDetaching([$user->id => ['role_id' => $role->id, 'role' => $data['role']]]);
                $request->attributes->get('current_company')->users()->updateExistingPivot($user->id, ['role_id' => $role->id, 'role' => $data['role']]);
            }
        }

        $user->save();

        return $this->success($this->serializeUser($user, $request->attributes->get('current_company')->id), 'Usuario atualizado com sucesso.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($this->canManageUsers($request), 403);
        abort_if($request->user()->id === $user->id, 422, 'Voce nao pode remover o proprio usuario.');
        abort_unless($this->userBelongsToCurrentCompany($request, $user) || $user->is_superadmin, 404);

        $request->attributes->get('current_company')->users()->detach($user->id);

        return $this->success(null, 'Usuario removido com sucesso.');
    }

    public function profile(Request $request)
    {
        return $this->success($this->serializeUser($request->user(), $request->attributes->get('current_company')->id));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $request->user()->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $request->user()->password = $data['password'];
        }

        $request->user()->save();

        return $this->success($this->serializeUser($request->user(), $request->attributes->get('current_company')->id), 'Perfil atualizado com sucesso.');
    }

    private function availableRoles(Request $request): array
    {
        return $request->user()->isSuperAdmin() ? [...$this->companyRoles, 'super_admin'] : $this->companyRoles;
    }

    private function canManageUsers(Request $request): bool
    {
        if ($request->user()->isSuperAdmin()) {
            return true;
        }

        return in_array($this->currentRole($request), ['company_admin'], true);
    }

    private function currentRole(Request $request): ?string
    {
        $company = $request->attributes->get('current_company');
        $roleId = $request->user()->companies()->where('companies.id', $company->id)->first()?->pivot?->role_id;

        $companyUser = $request->user()->companies()->where('companies.id', $company->id)->first();
        return $companyUser?->pivot?->role ?: ($roleId ? Role::find($roleId)?->name : null);
    }

    private function roleForCompany(int $companyId, string $name): Role
    {
        return Role::firstOrCreate(['company_id' => $companyId, 'name' => $name]);
    }

    private function userBelongsToCurrentCompany(Request $request, User $user): bool
    {
        return $user->companies()->where('companies.id', $request->attributes->get('current_company')->id)->exists();
    }

    private function serializeUser(User $user, int $companyId): array
    {
        $roleId = $user->companies()->where('companies.id', $companyId)->first()?->pivot?->role_id;
        $companyUser = $user->companies()->where('companies.id', $companyId)->first();
        $role = $user->isSuperAdmin() ? 'super_admin' : ($companyUser?->pivot?->role ?: ($roleId ? Role::find($roleId)?->name : null));

        return [
            ...$user->only(['id', 'name', 'email', 'status']),
            'role' => $role,
            'is_superadmin' => $user->is_superadmin,
        ];
    }
}
