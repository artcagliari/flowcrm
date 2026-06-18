<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Company;
use App\Services\CrmOverdueMarker;
use App\Services\GoogleCalendarService;
use App\Services\SequenceRunner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:mark-overdue', function (CrmOverdueMarker $marker) {
    Company::query()->where('status', 'active')->each(fn (Company $company) => $marker->markForCompany($company->id));
    $this->info('Registros vencidos atualizados.');
})->purpose('Mark overdue CRM tasks, payments and expenses');

Schedule::command('crm:mark-overdue')->hourly()->withoutOverlapping();

Artisan::command('crm:run-sequences', function (SequenceRunner $runner) {
    $count = $runner->processDue();
    $this->info("Sequencias processadas: {$count}");
})->purpose('Process follow-up sequence enrollments');

Artisan::command('crm:sync-calendars', function (GoogleCalendarService $calendar) {
    $total = 0;
    Company::query()->where('status', 'active')->each(function (Company $company) use ($calendar, &$total) {
        $total += $calendar->syncAppointments($company->id);
    });
    $this->info("Compromissos sincronizados: {$total}");
})->purpose('Sync upcoming appointments to Google Calendar');

Schedule::command('crm:run-sequences')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('crm:sync-calendars')->hourly()->withoutOverlapping();
