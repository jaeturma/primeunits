<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListingBoostController extends Controller
{
    public function boostListing(Request $request, Listing $listing, Plan $plan): RedirectResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        abort_unless($plan->type === Plan::TypeBoost && $plan->is_active, 404);

        $activeBoostExists = $listing->boosts()
            ->where('is_active', true)
            ->where('ends_at', '>', now())
            ->exists();

        if ($activeBoostExists) {
            return back()->withErrors([
                'listing_id' => 'This listing already has an active boost.',
            ]);
        }

        $pendingBoostExists = $listing->boosts()
            ->whereHas('payment', fn ($query) => $query->where('status', Payment::StatusPending))
            ->exists();

        if ($pendingBoostExists) {
            return back()->withErrors([
                'listing_id' => 'This listing already has a pending boost payment.',
            ]);
        }

        $boost = $listing->boosts()->create([
            'plan_id' => $plan->id,
            'is_active' => false,
        ]);

        $payment = $boost->payment()->create([
            'user_id' => $request->user()->id,
            'amount' => $plan->price,
            'method' => Payment::MethodGcash,
            'status' => Payment::StatusPending,
        ]);

        return to_route('payments.show', $payment);
    }
}
