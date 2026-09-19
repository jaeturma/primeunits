<?php

namespace App\Http\Controllers;

use App\Models\ListingBoost;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('seller/monetization/index', [
            'boostPlans' => Plan::query()
                ->where('type', Plan::TypeBoost)
                ->where('is_active', true)
                ->orderBy('price')
                ->get()
                ->map(fn (Plan $plan): array => $this->serializePlan($plan)),
            'subscriptionPlans' => Plan::query()
                ->where('type', Plan::TypeSubscription)
                ->where('is_active', true)
                ->orderBy('price')
                ->get()
                ->map(fn (Plan $plan): array => $this->serializePlan($plan)),
            'subscriptions' => $user->subscriptions()
                ->with(['plan', 'payment'])
                ->latest()
                ->get()
                ->map(fn (Subscription $subscription): array => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'starts_at' => $subscription->starts_at?->toISOString(),
                    'ends_at' => $subscription->ends_at?->toISOString(),
                    'plan' => $this->serializePlan($subscription->plan),
                    'payment' => $this->serializePayment($subscription->payment),
                ]),
            'boosts' => ListingBoost::query()
                ->with(['plan', 'payment', 'listing:id,title,user_id'])
                ->whereHas('listing', fn ($query) => $query->where('user_id', $user->id))
                ->latest()
                ->get()
                ->map(fn (ListingBoost $boost): array => [
                    'id' => $boost->id,
                    'starts_at' => $boost->starts_at?->toISOString(),
                    'ends_at' => $boost->ends_at?->toISOString(),
                    'is_active' => $boost->isCurrentlyActive(),
                    'plan' => $this->serializePlan($boost->plan),
                    'listing' => $boost->listing,
                    'payment' => $this->serializePayment($boost->payment),
                ]),
            'listings' => $user->listings()
                ->where('status', 'approved')
                ->latest()
                ->get(['id', 'title']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePlan(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'type' => $plan->type,
            'price' => $plan->price,
            'duration_days' => $plan->duration_days,
            'features' => $plan->features ?? [],
            'is_active' => $plan->is_active,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializePayment(?Payment $payment): ?array
    {
        if (! $payment) {
            return null;
        }

        return [
            'id' => $payment->id,
            'amount' => $payment->amount,
            'method' => $payment->method,
            'reference_number' => $payment->reference_number,
            'status' => $payment->status,
        ];
    }
}
