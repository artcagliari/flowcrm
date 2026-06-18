<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\CompanyIntegration;
use App\Services\GoogleCalendarOAuthService;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success(
            CompanyIntegration::where('company_id', $this->companyId($request))->get()
        );
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'credentials' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $integration = CompanyIntegration::updateOrCreate(
            ['company_id' => $this->companyId($request), 'provider' => $data['provider']],
            ['credentials' => $data['credentials'] ?? [], 'is_active' => $data['is_active'] ?? false]
        );

        return $this->success($integration, 'Integracao salva.');
    }

    public function connectGoogleCalendar(Request $request, GoogleCalendarOAuthService $oauth)
    {
        if (! $oauth->isConfigured()) {
            return $this->error('Google OAuth nao configurado no servidor. Defina GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET e GOOGLE_REDIRECT_URI.', [], 422);
        }

        $state = Str::random(48);
        Cache::put("google_oauth:{$state}", [
            'company_id' => $this->companyId($request),
            'user_id' => $request->user()->id,
        ], now()->addMinutes(10));

        return $this->success([
            'url' => $oauth->authorizationUrl($state),
        ]);
    }

    public function googleCalendarCallback(Request $request, GoogleCalendarOAuthService $oauth)
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

        if ($request->filled('error')) {
            return redirect("{$frontend}/integrations?google=error&message=".urlencode($request->query('error')));
        }

        $state = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');
        $context = Cache::pull("google_oauth:{$state}");

        if (! $context || $code === '') {
            return redirect("{$frontend}/integrations?google=error&message=invalid_state");
        }

        try {
            $tokens = $oauth->exchangeCode($code);
            $email = $oauth->fetchAccountEmail($tokens['access_token']);

            CompanyIntegration::updateOrCreate(
                ['company_id' => $context['company_id'], 'provider' => 'google_calendar'],
                [
                    'is_active' => true,
                    'credentials' => [
                        'access_token' => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'] ?? null,
                        'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600))->toIso8601String(),
                        'calendar_id' => 'primary',
                        'connected_email' => $email,
                        'connected_at' => now()->toIso8601String(),
                    ],
                ]
            );

            $synced = app(GoogleCalendarService::class)->syncAppointments((int) $context['company_id']);

            return redirect("{$frontend}/integrations?google=connected&synced={$synced}");
        } catch (\Throwable $e) {
            return redirect("{$frontend}/integrations?google=error&message=".urlencode('oauth_failed'));
        }
    }

    public function disconnectGoogleCalendar(Request $request)
    {
        CompanyIntegration::where('company_id', $this->companyId($request))
            ->where('provider', 'google_calendar')
            ->delete();

        return $this->success(null, 'Conta Google desconectada.');
    }

    public function updateGoogleCalendar(Request $request)
    {
        $data = $request->validate([
            'calendar_id' => ['required', 'string', 'max:255'],
        ]);

        $integration = CompanyIntegration::where('company_id', $this->companyId($request))
            ->where('provider', 'google_calendar')
            ->firstOrFail();

        $credentials = $integration->credentials ?? [];
        $credentials['calendar_id'] = $data['calendar_id'];
        $integration->update(['credentials' => $credentials]);

        return $this->success($integration, 'Agenda Google atualizada.');
    }

    public function syncGoogleCalendar(Request $request, GoogleCalendarService $calendar)
    {
        $synced = $calendar->syncAppointments($this->companyId($request));

        return $this->success(['synced' => $synced], "{$synced} compromissos sincronizados.");
    }

    private function companyId(Request $request): int
    {
        return (int) $request->attributes->get('current_company')->id;
    }
}
