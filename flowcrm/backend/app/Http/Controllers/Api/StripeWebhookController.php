<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $secret = config('services.stripe.webhook_secret');

        if ($secret) {
            $signature = $request->header('Stripe-Signature');
            abort_unless($this->verifyStripeSignature($payload, $signature, $secret), 400);
        }

        $event = $request->json()->all();
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];

        match ($type) {
            'customer.subscription.updated', 'customer.subscription.created' => $this->syncSubscription($object),
            'customer.subscription.deleted' => $this->cancelSubscription($object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function syncSubscription(array $object): void
    {
        $customerId = $object['customer'] ?? null;
        $company = Company::where('stripe_customer_id', $customerId)->first();

        if (! $company) {
            return;
        }

        Subscription::where('company_id', $company->id)->latest()->first()?->update([
            'stripe_subscription_id' => $object['id'] ?? null,
            'stripe_status' => $object['status'] ?? null,
            'status' => ($object['status'] ?? '') === 'active' ? 'active' : 'trial',
        ]);
    }

    private function cancelSubscription(array $object): void
    {
        Subscription::where('stripe_subscription_id', $object['id'] ?? null)->update(['status' => 'cancelled', 'stripe_status' => 'cancelled']);
    }

    private function verifyStripeSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (! $signature) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $item) {
            [$k, $v] = array_pad(explode('=', $item, 2), 2, null);
            $parts[$k] = $v;
        }

        if (empty($parts['t']) || empty($parts['v1'])) {
            return false;
        }

        $signed = hash_hmac('sha256', $parts['t'].'.'.$payload, $secret);

        return hash_equals($signed, $parts['v1']);
    }
}
