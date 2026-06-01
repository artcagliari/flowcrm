<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAdminCompanyRequest;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCompanyController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $query = Company::query()->withCount('users')->with(['users' => fn ($q) => $q->wherePivot('is_owner', true)->limit(1)]);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('plan_name')) {
            $query->where('plan_name', $request->query('plan_name'));
        }

        return $this->success($query->latest()->paginate((int) $request->query('per_page', 15)));
    }

    public function store(StoreAdminCompanyRequest $request)
    {
        [$company, $admin] = DB::transaction(function () use ($request) {
            $companyData = $request->validated('company');
            $adminData = $request->validated('admin');

            $company = Company::create([
                ...$companyData,
                'owner_name' => $adminData['name'],
            ]);

            $admin = User::create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => $adminData['password'],
                'phone' => $adminData['phone'] ?? null,
                'role' => 'company_admin',
                'status' => 'active',
            ]);

            $role = Role::firstOrCreate(['company_id' => $company->id, 'name' => 'company_admin']);
            foreach (['employee', 'financial', 'viewer'] as $roleName) {
                Role::firstOrCreate(['company_id' => $company->id, 'name' => $roleName]);
            }

            $company->users()->attach($admin->id, [
                'role_id' => $role->id,
                'role' => 'company_admin',
                'is_owner' => true,
                'status' => 'active',
            ]);

            $this->createDefaultSettings($company->id);
            Activity::create([
                'company_id' => $company->id,
                'user_id' => $request->user()->id,
                'action' => 'company_created',
                'description' => 'Empresa criada pelo Admin Master.',
            ]);

            return [$company, $admin];
        });

        return $this->success([
            'company' => $company->loadCount('users'),
            'admin' => $admin->only(['id', 'name', 'email', 'phone', 'role', 'status']),
        ], 'Empresa criada com sucesso. O acesso do administrador ja esta disponivel.', 201);
    }

    public function show(Company $company)
    {
        return $this->success($company->load(['users'])->loadCount('users'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'type' => ['required', Rule::in(['company', 'autonomous'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'plan_name' => ['nullable', 'string', 'max:120'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $company->update($data);

        return $this->success($company->fresh()->loadCount('users'), 'Empresa atualizada com sucesso.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return $this->success(null, 'Empresa excluida com sucesso.');
    }

    public function activate(Company $company) { return $this->setStatus($company, 'active'); }
    public function suspend(Company $company) { return $this->setStatus($company, 'suspended'); }
    public function deactivate(Company $company) { return $this->setStatus($company, 'inactive'); }

    public function resetPassword(Request $request, Company $company)
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $admin = $company->users()->wherePivot('is_owner', true)->first();

        if (! $admin) {
            return $this->error('Administrador da empresa nao encontrado.', [], 404);
        }

        $admin->update(['password' => $data['password']]);

        return $this->success(null, 'Senha redefinida com sucesso.');
    }

    private function setStatus(Company $company, string $status)
    {
        $company->update(['status' => $status]);

        return $this->success($company->fresh(), 'Status atualizado com sucesso.');
    }

    private function createDefaultSettings(int $companyId): void
    {
        foreach ([
            'timezone' => ['America/Sao_Paulo', 'string'],
            'currency' => ['BRL', 'string'],
            'date_format' => ['dd/MM/yyyy', 'string'],
            'time_format' => ['HH:mm', 'string'],
            'language' => ['pt-BR', 'string'],
            'theme' => ['dark', 'string'],
        ] as $key => [$value, $type]) {
            Setting::updateOrCreate(
                ['company_id' => $companyId, 'setting_key' => $key],
                ['key' => $key, 'value' => $value, 'setting_value' => $value, 'type' => $type]
            );
        }
    }
}
