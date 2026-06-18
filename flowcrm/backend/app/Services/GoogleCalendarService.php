<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\CompanyIntegration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    public function __construct(private GoogleCalendarOAuthService $oauth) {}

    public function syncAppointments(int $companyId): int
    {
        $integration = $this->activeIntegration($companyId);
        if (! $integration) {
            return 0;
        }

        $synced = 0;
        Appointment::where('company_id', $companyId)
            ->where('starts_at', '>=', now())
            ->whereNull('google_event_id')
            ->orderBy('starts_at')
            ->limit(50)
            ->each(function (Appointment $appointment) use (&$synced) {
                if ($this->pushAppointment($appointment)) {
                    $synced++;
                }
            });

        return $synced;
    }

    public function pushAppointment(Appointment $appointment): bool
    {
        if ($appointment->google_event_id || ! $appointment->starts_at) {
            return false;
        }

        $integration = $this->activeIntegration((int) $appointment->company_id);
        if (! $integration) {
            return false;
        }

        $token = $this->resolveAccessToken($integration);
        if (! $token) {
            return false;
        }

        $start = $appointment->starts_at;
        $end = $appointment->ends_at ?? $start->copy()->addHour();
        $calendarId = $integration->credentials['calendar_id'] ?? 'primary';

        try {
            $response = Http::withToken($token)->post(
                'https://www.googleapis.com/calendar/v3/calendars/'.urlencode($calendarId).'/events',
                [
                    'summary' => $appointment->title,
                    'description' => $appointment->description,
                    'start' => ['dateTime' => $start->toIso8601String()],
                    'end' => ['dateTime' => $end->toIso8601String()],
                ]
            );

            if ($response->successful()) {
                $appointment->update(['google_event_id' => $response->json('id')]);

                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Google Calendar sync failed', ['appointment_id' => $appointment->id, 'error' => $e->getMessage()]);
        }

        return false;
    }

    public function activeIntegration(int $companyId): ?CompanyIntegration
    {
        $integration = CompanyIntegration::where('company_id', $companyId)
            ->where('provider', 'google_calendar')
            ->where('is_active', true)
            ->first();

        if (! $integration) {
            return null;
        }

        $credentials = $integration->credentials ?? [];

        return (! empty($credentials['access_token']) || ! empty($credentials['refresh_token']))
            ? $integration
            : null;
    }

    public function resolveAccessToken(CompanyIntegration $integration): ?string
    {
        $credentials = $integration->credentials ?? [];
        $accessToken = $credentials['access_token'] ?? null;
        $refreshToken = $credentials['refresh_token'] ?? null;
        $expiresAt = ! empty($credentials['expires_at']) ? Carbon::parse($credentials['expires_at']) : null;

        if ($accessToken && (! $expiresAt || $expiresAt->isAfter(now()->addMinute()))) {
            return $accessToken;
        }

        if (! $refreshToken || ! $this->oauth->isConfigured()) {
            return $accessToken;
        }

        try {
            $tokens = $this->oauth->refreshAccessToken($refreshToken);
            $credentials['access_token'] = $tokens['access_token'];
            $credentials['expires_at'] = now()->addSeconds((int) ($tokens['expires_in'] ?? 3600))->toIso8601String();
            if (! empty($tokens['refresh_token'])) {
                $credentials['refresh_token'] = $tokens['refresh_token'];
            }
            $integration->update(['credentials' => $credentials]);

            return $credentials['access_token'];
        } catch (\Throwable $e) {
            Log::warning('Google token refresh failed', ['company_id' => $integration->company_id, 'error' => $e->getMessage()]);

            return $accessToken;
        }
    }
}
