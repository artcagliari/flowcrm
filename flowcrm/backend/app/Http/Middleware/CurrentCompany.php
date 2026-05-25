<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-ID') ?: $request->query('company_id');

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.',
                'errors' => [],
            ], 401);
        }

        $company = $companyId
            ? $user->companies()->whereKey($companyId)->first()
            : $user->companies()->first();

        if (! $company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa atual inválida ou não informada.',
                'errors' => ['company_id' => ['Informe uma empresa vinculada ao usuário.']],
            ], 403);
        }

        app()->instance(Company::class, $company);
        $request->attributes->set('current_company', $company);

        return $next($request);
    }
}
