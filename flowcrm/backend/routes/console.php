<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Company;
use App\Services\CrmOverdueMarker;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:mark-overdue', function (CrmOverdueMarker $marker) {
    Company::query()->where('status', 'active')->each(fn (Company $company) => $marker->markForCompany($company->id));
    $this->info('Registros vencidos atualizados.');
})->purpose('Mark overdue CRM tasks, payments and expenses');
