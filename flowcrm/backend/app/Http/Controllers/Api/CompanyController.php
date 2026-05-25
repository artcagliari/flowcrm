<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCompanyRequest;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use RespondsWithJson;

    public function show(Request $request)
    {
        return $this->success($request->attributes->get('current_company'));
    }

    public function update(UpdateCompanyRequest $request)
    {
        $company = $request->attributes->get('current_company');
        $company->update($request->validated());
        return $this->success($company->fresh(), 'Empresa atualizada.');
    }
}
