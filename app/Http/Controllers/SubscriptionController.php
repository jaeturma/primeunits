<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless($plan->type === Plan::TypeSubscription && $plan->is_active, 404);

        $subscription = Subscription::query()->create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'status' => Subscription::StatusPending,
        ]);

        $payment = $subscription->payment()->create([
            'user_id' => $request->user()->id,
            'amount' => $plan->price,
            'method' => Payment::MethodGcash,
            'status' => Payment::StatusPending,
        ]);

        return to_route('payments.show', $payment);
    }

    public function mySubscriptions(Request $request): RedirectResponse
    {
        return to_route('seller.monetization.index');
    }
}
