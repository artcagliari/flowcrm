<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Task;
use App\Policies\AppointmentPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\LeadPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\TaskPolicy;
use App\Services\Whatsapp\EvolutionApiProvider;
use App\Services\Whatsapp\LogWhatsappProvider;
use App\Services\Whatsapp\MetaCloudApiProvider;
use App\Services\Whatsapp\WhatsappProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    private array $policies = [
        Client::class => ClientPolicy::class,
        Lead::class => LeadPolicy::class,
        Task::class => TaskPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Payment::class => PaymentPolicy::class,
        Expense::class => ExpensePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WhatsappProvider::class, function () {
            $config = config('services.whatsapp');

            return match ($config['provider'] ?? 'log') {
                'evolution' => new EvolutionApiProvider($config['evolution'] ?? []),
                'meta' => new MetaCloudApiProvider($config['meta'] ?? []),
                default => new LogWhatsappProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
